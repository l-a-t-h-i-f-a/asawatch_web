<?php

namespace App\Services\Ekspor;

use App\Models\Sesi;
use App\Models\User;
use App\Support\SaringanEkspor;
use App\Support\Waktu;
use DateTimeInterface;
use LogicException;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\DateTimeCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Menulis berkas .xlsx multi-lembar dari data yang tercakup saringan.
 *
 * Kenapa xlsx dan bukan sekadar CSV kedua: angka ditulis sebagai sel numerik
 * dan waktu sebagai sel tanggal Excel, jadi persoalan pemisah koma/titik-koma,
 * BOM, dan desimal koma-vs-titik yang menghantui CSV tidak ada di sini.
 *
 * Berkas dirakit baris demi baris ke berkas sementara (xlsx adalah arsip ZIP,
 * jadi tidak bisa benar-benar dialirkan ke php://output seperti CSV) namun
 * tetap dengan memori tetap: satu potongan 50 responden sekali jalan.
 */
final class PenyusunXlsx
{
    private const LEMBAR_PANDUAN = 'Baca Ini Dulu';

    private const LEMBAR_KAMUS = 'Kamus Data';

    private Style $gayaHeader;

    private Style $gayaWaktu;

    private Style $gayaTanggal;

    private Style $gayaJudul;

    /** @var array<string, Sheet> */
    private array $lembar = [];

    /** @var array<string, list<string>> tipe kolom per lembar, untuk memilih gaya sel */
    private array $tipe = [];

    public function __construct(private readonly SaringanEkspor $saringan)
    {
        $this->gayaHeader = (new Style)->setFontBold()->setBackgroundColor('EAF7F0');
        $this->gayaWaktu = (new Style)->setFormat('yyyy-mm-dd hh:mm');
        $this->gayaTanggal = (new Style)->setFormat('yyyy-mm-dd');
        $this->gayaJudul = (new Style)->setFontBold()->setFontSize(13);
    }

    public function tulis(string $path, RingkasanEkspor $ringkasan): void
    {
        $writer = new Writer(new Options);
        $writer->setCreator('AsaWatch');
        $writer->openToFile($path);

        try {
            $this->siapkanPanduan($writer, $ringkasan);
            $this->siapkanLembarData($writer);
            $this->siapkanKamus($writer);
            $this->isi($writer);
            $this->pasangAutoFilter();
        } finally {
            $writer->close();
        }
    }

    // ---------------------------------------------------------------- lembar

    /**
     * Lembar pertama yang dilihat orang saat membuka berkas: konteks ekspor,
     * urutan membaca, daftar lembar, dan aturan main isi sel.
     *
     * Sengaja pendek — satu layar. Panduan yang harus digulir tidak akan
     * dibaca, dan penjelasan panjang per kolom sudah punya tempatnya sendiri
     * di lembar "Kamus Data".
     */
    private function siapkanPanduan(Writer $writer, RingkasanEkspor $ringkasan): void
    {
        $lembar = $writer->getCurrentSheet();
        $lembar->setName(self::LEMBAR_PANDUAN);
        $lembar->setColumnWidth(24, 1);
        $lembar->setColumnWidth(62, 2);
        $lembar->setColumnWidth(12, 3);

        $judul = fn (string $teks) => $writer->addRow(new Row([Cell::fromValue($teks, $this->gayaJudul)]));
        $baris = fn (...$isi) => $writer->addRow($this->barisBebas($isi, tebalKolomPertama: true));
        $kosong = fn () => $writer->addRow(new Row([]));

        $judul('Ekspor Data Penelitian AsaWatch');
        $baris('Diekspor pada', Waktu::tanggalJam(now()));
        $baris('Lingkup responden', $this->saringan->lingkup->label());
        $baris('Periode', $this->saringan->labelPeriode());
        $baris('Sesi uji', $this->saringan->sertakanSesiUji ? 'Disertakan' : 'Tidak disertakan');
        $kosong();

        $judul('Mulai dari mana');
        $baris('1.', 'Buka lembar "'.SkemaEkspor::LEMBAR_RINGKASAN.'" — satu baris = satu sesi makan.');
        $baris('2.', 'Butuh angka mentah untuk SPSS/R? Pakai "'.SkemaEkspor::LEMBAR_PENGUKURAN.'" atau "'.SkemaEkspor::LEMBAR_PENGUKURAN_LEBAR.'".');
        $baris('3.', 'Arti tiap kolom ada di lembar "'.self::LEMBAR_KAMUS.'", paling akhir.');
        $kosong();

        $judul('Isi berkas ini');
        $writer->addRow(Row::fromValues(['Lembar', 'Untuk apa', 'Jumlah baris'], $this->gayaHeader));

        $penjelasan = SkemaEkspor::penjelasan();

        foreach ($ringkasan->perLembar() as $nama => $jumlah) {
            $writer->addRow(new Row([
                Cell::fromValue($nama, $this->gayaHeader),
                Cell::fromValue($penjelasan[$nama] ?? ''),
                Cell::fromValue($jumlah),
            ]));
        }

        $kosong();

        $judul('Cara membaca');
        $baris('Sel kosong', 'Nilainya tidak ada — bukan nol.');
        $baris('Kolom 1/0', '1 = ya, 0 = tidak.');
        $baris('_wib dan _utc', 'Waktu yang sama dalam dua zona ('.Waktu::LABEL_ZONA.' dan UTC).');
        $baris('Sesi uji', 'Jadwalnya dimampatkan, jadi label -30 mnt / +1 jam / +2 jam tidak berlaku. Jarak sebenarnya: kolom detik_s0–detik_s3 di lembar "'.SkemaEkspor::LEMBAR_PENGUKURAN_LEBAR.'".');
        $baris('Menggabungkan lembar', 'Cocokkan lewat sesi_id dan user_id.');
    }

    private function siapkanKamus(Writer $writer): void
    {
        $lembar = $writer->addNewSheetAndMakeItCurrent();
        $lembar->setName(self::LEMBAR_KAMUS);
        $lembar->setSheetView((new SheetView)->setFreezeRow(2));
        $lembar->setColumnWidth(18, 1);
        $lembar->setColumnWidth(26, 2);
        $lembar->setColumnWidth(11, 3);
        $lembar->setColumnWidth(10, 4);
        $lembar->setColumnWidth(70, 5);

        $writer->addRow(Row::fromValues(['lembar', 'kolom', 'tipe', 'satuan', 'keterangan'], $this->gayaHeader));

        foreach (SkemaEkspor::kamus() as $baris) {
            $writer->addRow(Row::fromValues($baris));
        }
    }

    private function siapkanLembarData(Writer $writer): void
    {
        foreach (SkemaEkspor::lembar() as $nama => $kolom) {
            $lembar = $writer->addNewSheetAndMakeItCurrent();
            $lembar->setName($nama);
            $lembar->setSheetView((new SheetView)->setFreezeRow(2));
            $lembar->setColumnWidthForRange(20, 1, count($kolom));

            $writer->addRow(Row::fromValues(array_keys($kolom), $this->gayaHeader));

            $this->lembar[$nama] = $lembar;
            $this->tipe[$nama] = array_map(fn (array $d) => $d[0], array_values($kolom));
        }
    }

    /**
     * AutoFilter butuh nomor baris terakhir, yang baru diketahui setelah semua
     * baris ditulis — makanya dipasang belakangan, sebelum close().
     */
    private function pasangAutoFilter(): void
    {
        foreach ($this->lembar as $nama => $lembar) {
            $jumlahBaris = $lembar->getWrittenRowCount();

            if ($jumlahBaris < 2) {
                continue;
            }

            $lembar->setAutoFilter(new AutoFilter(0, 1, count($this->tipe[$nama]) - 1, $jumlahBaris));
        }
    }

    // ------------------------------------------------------------------- isi

    private function isi(Writer $writer): void
    {
        $aliran = new AliranResponden($this->saringan);

        $aliran->setiap([
            'profil',
            'sesi.sampel',
            'sesi.hasilDeteksi',
            'sesi.itemMakanan',
        ], function (User $user) use ($writer) {
            $this->tulisKe($writer, SkemaEkspor::LEMBAR_RINGKASAN,
                $user->sesi->map(fn (Sesi $s) => $this->barisRingkasan($user, $s))->all());

            $this->tulisKe($writer, SkemaEkspor::LEMBAR_RESPONDEN, [$this->barisResponden($user)]);

            $this->tulisKe($writer, SkemaEkspor::LEMBAR_SESI,
                $user->sesi->map(fn (Sesi $s) => $this->barisSesi($user, $s))->all());

            $this->tulisKe($writer, SkemaEkspor::LEMBAR_PENGUKURAN,
                $user->sesi->flatMap(fn (Sesi $s) => $s->sampel->map(fn ($sampel) => $this->barisPengukuran($user, $s, $sampel)))->all());

            $this->tulisKe($writer, SkemaEkspor::LEMBAR_PENGUKURAN_LEBAR,
                $user->sesi->map(fn (Sesi $s) => $this->barisLebar($user, $s))->all());

            $this->tulisKe($writer, SkemaEkspor::LEMBAR_ITEM_MAKANAN,
                $user->sesi->flatMap(fn (Sesi $s) => $s->itemMakanan->map(fn ($item) => $this->barisItem($user, $s, $item)))->all());
        });
    }

    /** @param  list<Row>  $baris */
    private function tulisKe(Writer $writer, string $lembar, array $baris): void
    {
        if ($baris === []) {
            return;
        }

        $writer->setCurrentSheet($this->lembar[$lembar]);
        $writer->addRows($baris);
    }

    // ----------------------------------------------------------------- baris

    /**
     * Baris lembar ramah-baca. Slot dibaca lewat kolom `index` (0 = -30 mnt,
     * 1 = t0, 2 = +1 jam, 3 = +2 jam menurut jadwal kanonik), bukan lewat
     * detik_relatif_t0 — sesi uji boleh memampatkan jadwal, dan yang tetap
     * stabil hanyalah nomor slotnya.
     */
    private function barisRingkasan(User $user, Sesi $sesi): Row
    {
        $slot = $sesi->sampel->keyBy('index');
        $gula = fn (int $i) => $slot->get($i)?->gula_darah;
        $saatMakan = $slot->get(1);
        $hasil = $sesi->hasilDeteksi;

        $tekanan = $saatMakan?->sistolik !== null && $saatMakan?->diastolik !== null
            ? $saatMakan->sistolik.'/'.$saatMakan->diastolik
            : null;

        return $this->baris(SkemaEkspor::LEMBAR_RINGKASAN, [
            $user->nama,
            Waktu::wib($sesi->waktu_foto),
            $sesi->sesi_uji ? 'Sesi uji' : 'Sesi biasa',
            $sesi->status,
            $gula(0),
            $gula(1),
            $gula(2),
            $gula(3),
            $sesi->sampel->where('status', 'terisi')->count(),
            $tekanan,
            $saatMakan?->detak_jantung,
            $saatMakan?->spo2,
            $sesi->itemMakanan->pluck('nama')->join(', '),
            $hasil?->total_kalori,
            $hasil?->total_karbohidrat,
            $hasil?->total_gula_total,
            $hasil?->indeks_glikemik_perkiraan,
            $sesi->id,
        ]);
    }

    private function barisResponden(User $user): Row
    {
        $profil = $user->profil;

        return $this->baris(SkemaEkspor::LEMBAR_RESPONDEN, [
            $user->id,
            $user->nama,
            $user->email,
            Waktu::wib($user->created_at),
            $profil?->tanggal_lahir,
            $profil?->jenis_kelamin,
            $profil?->golongan_darah,
            $profil?->tinggi_cm,
            $this->angka($profil?->berat_kg),
            $user->sesi->count(),
        ]);
    }

    private function barisSesi(User $user, Sesi $sesi): Row
    {
        $hasil = $sesi->hasilDeteksi;

        return $this->baris(SkemaEkspor::LEMBAR_SESI, [
            $sesi->id,
            $user->id,
            $user->nama,
            Waktu::wib($sesi->waktu_foto),
            Waktu::iso($sesi->waktu_foto),
            Waktu::wib($sesi->t0),
            Waktu::iso($sesi->t0),
            $sesi->status,
            $sesi->waktu_tidak_pasti,
            $sesi->sesi_uji,
            $hasil?->indeks_glikemik_perkiraan,
            $hasil?->keyakinan,
            $hasil?->dikoreksi_user,
            $hasil?->total_kalori,
            $hasil?->total_karbohidrat,
            $hasil?->total_protein,
            $hasil?->total_lemak,
            $hasil?->total_gula_total,
            $hasil?->total_serat,
            implode(', ', $hasil?->zat_tidak_lengkap ?? []),
            $sesi->itemMakanan->count(),
        ]);
    }

    private function barisPengukuran(User $user, Sesi $sesi, $sampel): Row
    {
        return $this->baris(SkemaEkspor::LEMBAR_PENGUKURAN, [
            $user->id,
            $user->nama,
            $user->email,
            $sesi->id,
            Waktu::wib($sesi->waktu_foto),
            $sesi->status,
            $sesi->sesi_uji,
            $sampel->index,
            $sampel->detik_relatif_t0,
            round($sampel->detik_relatif_t0 / 60, 2),
            $sampel->status,
            $sampel->dari_buffer,
            $sampel->gula_darah,
            $sampel->detak_jantung,
            $sampel->sistolik,
            $sampel->diastolik,
            $sampel->spo2,
        ]);
    }

    /**
     * Satu baris per sesi dengan keempat slot dibentangkan menyamping —
     * bentuk yang dipakai uji berpasangan / repeated measures.
     */
    private function barisLebar(User $user, Sesi $sesi): Row
    {
        $slot = $sesi->sampel->keyBy('index');

        $nilai = [
            $sesi->id,
            $user->id,
            $user->nama,
            Waktu::wib($sesi->waktu_foto),
            $sesi->status,
            $sesi->sesi_uji,
            $sesi->sampel->where('status', 'terisi')->count(),
        ];

        for ($i = 0; $i < SkemaEkspor::SLOT_SAMPEL; $i++) {
            $nilai[] = $slot->get($i)?->detik_relatif_t0;
        }

        foreach (SkemaEkspor::metrikSampel() as $metrik) {
            for ($i = 0; $i < SkemaEkspor::SLOT_SAMPEL; $i++) {
                $nilai[] = $slot->get($i)?->{$metrik};
            }
        }

        return $this->baris(SkemaEkspor::LEMBAR_PENGUKURAN_LEBAR, $nilai);
    }

    private function barisItem(User $user, Sesi $sesi, $item): Row
    {
        return $this->baris(SkemaEkspor::LEMBAR_ITEM_MAKANAN, [
            $sesi->id,
            $user->id,
            $item->urutan,
            $item->nama,
            $item->sumber_gizi,
            $item->cocok,
            $item->porsi,
            $item->estimasi_gram,
            $item->kalori,
            $item->karbohidrat,
            $item->protein,
            $item->lemak,
            $item->gula_total,
            $item->serat,
        ]);
    }

    // ------------------------------------------------------------------ sel

    /**
     * Rakit satu baris memakai tipe kolom dari SkemaEkspor. Jumlah nilai yang
     * tidak cocok dengan definisi lembar dianggap galat pemrograman: itu
     * berarti skema dan penulis baris sudah tidak sejalan, dan berkasnya akan
     * bergeser kolom tanpa ada yang menyadari.
     */
    private function baris(string $lembar, array $nilai): Row
    {
        $tipe = $this->tipe[$lembar];
        $nilai = array_values($nilai);

        if (count($nilai) !== count($tipe)) {
            throw new LogicException(sprintf(
                'Lembar "%s" mendefinisikan %d kolom tapi barisnya berisi %d nilai.',
                $lembar, count($tipe), count($nilai)
            ));
        }

        return new Row(array_map(fn ($v, $t) => $this->sel($v, $t), $nilai, $tipe));
    }

    private function sel(mixed $nilai, string $tipe): Cell
    {
        if ($nilai instanceof DateTimeInterface) {
            return new DateTimeCell($nilai, $tipe === 'tanggal' ? $this->gayaTanggal : $this->gayaWaktu);
        }

        if (is_bool($nilai)) {
            return Cell::fromValue($nilai ? 1 : 0);
        }

        return Cell::fromValue($nilai);
    }

    /** @param  list<string>  $isi */
    private function barisBebas(array $isi, bool $tebalKolomPertama = false): Row
    {
        $sel = [];

        foreach (array_values($isi) as $i => $nilai) {
            $sel[] = Cell::fromValue($nilai, $tebalKolomPertama && $i === 0 ? $this->gayaHeader : null);
        }

        return new Row($sel);
    }

    private function angka(mixed $nilai): ?float
    {
        return $nilai === null ? null : (float) $nilai;
    }
}
