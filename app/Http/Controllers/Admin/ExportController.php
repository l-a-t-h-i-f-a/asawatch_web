<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Ekspor\AliranResponden;
use App\Services\Ekspor\FormatEkspor;
use App\Services\Ekspor\PenyusunXlsx;
use App\Services\Ekspor\RingkasanEkspor;
use App\Services\Ekspor\SkemaEkspor;
use App\Support\SaringanEkspor;
use App\Support\Waktu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Ekspor data penelitian dalam tiga bentuk, semuanya mengikuti saringan yang
 * sama (lihat App\Support\SaringanEkspor): lingkup responden, rentang
 * tanggal, dan dua sakelar penyertaan.
 *
 * - XLSX  — berkas kerja: satu lembar per entitas, plus Kamus Data.
 * - CSV   — satu baris per titik pengukuran, untuk SPSS/R/Python.
 * - JSON  — arsip mentah lengkap, kembar dari GET /api/v1/akun/ekspor.
 *
 * Akun administrator tidak pernah ikut karena tidak memiliki data pengukuran.
 *
 * Kolom sesi_uji ikut diekspor sebagai penanda sesi pengujian (jadwal
 * dimampatkan atau perangkat palsu). Penanda saja — tidak ada satu pun angka
 * di panel yang menyaring berdasarkan kolom ini.
 */
class ExportController extends Controller
{
    /** Direktori berkas xlsx sementara; dihapus segera setelah terkirim. */
    private const DIR_SEMENTARA = 'app/private/ekspor';

    public function index(Request $request)
    {
        $saringan = SaringanEkspor::dari($request);

        return view('admin.ekspor.index', [
            'active' => 'ekspor',
            'saringan' => $saringan,
            'lingkup' => $saringan->lingkup,
            'ringkasan' => RingkasanEkspor::hitung($saringan),
            'penjelasanLembar' => SkemaEkspor::penjelasan(),
            'format' => FormatEkspor::dari($request->query('format')),
            'semuaFormat' => FormatEkspor::cases(),
        ]);
    }

    /**
     * Satu pintu untuk tombol "Unduh" di halaman: bentuk berkas dipilih lewat
     * radio, jadi tombolnya cukup satu dan tidak butuh JavaScript untuk
     * menentukan tujuan. Ketiga rute langsung di bawah tetap ada sebagai
     * alamat yang jujur dan bisa ditandai.
     */
    public function download(Request $request)
    {
        return match (FormatEkspor::dari($request->query('format'))) {
            FormatEkspor::XLSX => $this->downloadXlsx($request),
            FormatEkspor::CSV => $this->downloadCsv($request),
            FormatEkspor::JSON => $this->downloadJson($request),
        };
    }

    public function downloadXlsx(Request $request): BinaryFileResponse
    {
        $saringan = SaringanEkspor::dari($request);

        // xlsx adalah arsip ZIP: tidak bisa dialirkan sepotong-sepotong ke
        // php://output seperti CSV, jadi dirakit dulu ke berkas sementara.
        // Memorinya tetap tetap — OpenSpout menulis per baris.
        File::ensureDirectoryExists(storage_path(self::DIR_SEMENTARA));
        $sementara = storage_path(self::DIR_SEMENTARA).'/'.uniqid('ekspor_', true).'.xlsx';

        try {
            (new PenyusunXlsx($saringan))->tulis($sementara, RingkasanEkspor::hitung($saringan));
        } catch (Throwable $e) {
            // Berkas yang gagal dirakit tidak pernah terkirim, jadi
            // deleteFileAfterSend() tidak akan membersihkannya sendiri.
            File::delete($sementara);

            throw $e;
        }

        return response()
            ->download($sementara, FormatEkspor::XLSX->namaBerkas($saringan), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend();
    }

    public function downloadJson(Request $request): StreamedResponse
    {
        $saringan = SaringanEkspor::dari($request);

        return response()->streamDownload(function () use ($saringan) {
            $out = fopen('php://output', 'w');

            fwrite($out, '{"diekspor_pada":'.json_encode(Waktu::iso(now())));
            fwrite($out, ',"lingkup":'.json_encode($saringan->lingkup->label()));
            fwrite($out, ',"saringan":'.json_encode([
                'periode' => $saringan->labelPeriode(),
                'dari' => Waktu::iso($saringan->dari),
                'sampai' => Waktu::iso($saringan->sampai),
                'sesi_uji_disertakan' => $saringan->sertakanSesiUji,
            ]));
            fwrite($out, ',"responden":[');

            // Ditulis per potong supaya ekspor lintas responden tidak menahan
            // seluruh dataset (sesi + sampel + item makanan) di memori.
            $pertama = true;

            (new AliranResponden($saringan))->setiap([
                'profil', 'sesi.sampel', 'sesi.hasilDeteksi.itemMakanan', 'kalibrasi', 'perangkat',
            ], function (User $user) use ($out, &$pertama) {
                fwrite($out, $pertama ? '' : ',');
                $pertama = false;

                fwrite($out, json_encode([
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'terdaftar_pada' => $user->created_at,
                    'profil' => $user->profil,
                    'sesi' => $user->sesi,
                    'kalibrasi' => $user->kalibrasi,
                    'perangkat' => $user->perangkat,
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            });

            fwrite($out, ']}');
            fclose($out);
        }, FormatEkspor::JSON->namaBerkas($saringan), ['Content-Type' => 'application/json']);
    }

    public function downloadCsv(Request $request): StreamedResponse
    {
        $saringan = SaringanEkspor::dari($request);

        // Excel berlokal Indonesia membaca CSV dengan pemisah titik koma,
        // sementara read.csv() / pandas menunggu koma. Tidak ada jawaban yang
        // benar untuk keduanya, jadi pilihannya diserahkan ke peneliti — koma
        // tetap bawaan supaya berkas lama terbaca sama.
        $pemisah = $request->query('pemisah') === 'titik_koma' ? ';' : ',';

        return response()->streamDownload(function () use ($saringan, $pemisah) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8: tanpa ini Excel membaca berkas sebagai ANSI dan nama
            // berhuruf non-ASCII berubah jadi sampah.
            fwrite($out, "\u{FEFF}");
            fputcsv($out, SkemaEkspor::header(SkemaEkspor::LEMBAR_PENGUKURAN), $pemisah);

            (new AliranResponden($saringan))->setiap(['sesi.sampel'], function (User $user) use ($out, $pemisah) {
                foreach ($user->sesi as $sesi) {
                    foreach ($sesi->sampel as $s) {
                        fputcsv($out, [
                            $user->id,
                            $user->nama,
                            $user->email,
                            $sesi->id,
                            Waktu::tanggal($sesi->waktu_foto, 'Y-m-d H:i'),
                            $sesi->status,
                            (int) $sesi->sesi_uji,
                            $s->index,
                            $s->detik_relatif_t0,
                            round($s->detik_relatif_t0 / 60, 2),
                            $s->status,
                            (int) $s->dari_buffer,
                            $s->gula_darah,
                            $s->detak_jantung,
                            $s->sistolik,
                            $s->diastolik,
                            $s->spo2,
                        ], $pemisah);
                    }
                }
            });

            fclose($out);
        }, FormatEkspor::CSV->namaBerkas($saringan), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
