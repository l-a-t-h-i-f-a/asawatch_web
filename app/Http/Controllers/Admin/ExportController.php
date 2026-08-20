<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perangkat;
use App\Models\Sampel;
use App\Models\Sesi;
use App\Models\User;
use App\Support\LingkupResponden;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ekspor data penelitian: seluruh responden, atau satu responden saja bila
 * dipilih lewat "?responden=". Akun administrator tidak pernah ikut karena
 * tidak memiliki data pengukuran.
 *
 * Kolom/field sesi_uji ikut diekspor sebagai penanda sesi pengujian (jadwal
 * dimampatkan atau perangkat palsu). Penanda saja — tidak ada satu pun angka
 * di panel yang menyaring berdasarkan kolom ini.
 */
class ExportController extends Controller
{
    private const KOLOM_CSV = [
        'user_id', 'nama', 'email', 'sesi_id', 'waktu_foto', 'status_sesi', 'sesi_uji',
        'index', 'detik_relatif_t0', 'status_sampel', 'gula_darah',
        'detak_jantung', 'sistolik', 'diastolik', 'spo2',
    ];

    public function index(Request $request)
    {
        $lingkup = LingkupResponden::dari($request);
        $ids = $lingkup->ids();

        return view('admin.ekspor.index', [
            'active' => 'ekspor',
            'lingkup' => $lingkup,
            'totalResponden' => $lingkup->jumlahResponden(),
            'totalSesi' => Sesi::whereIn('user_id', $ids)->count(),
            'totalSesiUji' => Sesi::whereIn('user_id', $ids)->where('sesi_uji', true)->count(),
            'totalTitikData' => Sampel::whereHas('sesi', fn ($q) => $q->whereIn('user_id', $ids))
                ->where('status', 'terisi')
                ->count(),
            'totalPerangkat' => Perangkat::whereIn('user_id', $ids)->count(),
        ]);
    }

    public function downloadJson(Request $request): StreamedResponse
    {
        $lingkup = LingkupResponden::dari($request);
        $nama = "asawatch_{$lingkup->slug()}_".now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () use ($lingkup) {
            $out = fopen('php://output', 'w');

            fwrite($out, '{"diekspor_pada":'.json_encode(now()->toIso8601String()));
            fwrite($out, ',"lingkup":'.json_encode($lingkup->label()));
            fwrite($out, ',"responden":[');

            // Ditulis per potong supaya ekspor lintas responden tidak menahan
            // seluruh dataset (sesi + sampel + item makanan) di memori.
            $pertama = true;
            $this->potongResponden($lingkup, function (User $user) use ($out, &$pertama) {
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
            }, ['profil', 'sesi.sampel', 'sesi.hasilDeteksi.itemMakanan', 'kalibrasi', 'perangkat']);

            fwrite($out, ']}');
            fclose($out);
        }, $nama, ['Content-Type' => 'application/json']);
    }

    public function downloadCsv(Request $request): StreamedResponse
    {
        $lingkup = LingkupResponden::dari($request);
        $nama = "asawatch_sampel_{$lingkup->slug()}_".now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($lingkup) {
            $out = fopen('php://output', 'w');
            fputcsv($out, self::KOLOM_CSV);

            $this->potongResponden($lingkup, function (User $user) use ($out) {
                foreach ($user->sesi as $sesi) {
                    foreach ($sesi->sampel as $s) {
                        fputcsv($out, [
                            $user->id,
                            $user->nama,
                            $user->email,
                            $sesi->id,
                            $sesi->waktu_foto?->toIso8601String(),
                            $sesi->status,
                            $sesi->sesi_uji ? 1 : 0,
                            $s->index,
                            $s->detik_relatif_t0,
                            $s->status,
                            $s->gula_darah,
                            $s->detak_jantung,
                            $s->sistolik,
                            $s->diastolik,
                            $s->spo2,
                        ]);
                    }
                }
            }, ['sesi.sampel']);

            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv']);
    }

    /**
     * Jalankan $tulis untuk tiap responden dalam lingkup, 50 akun sekaligus.
     *
     * Potongan diambil dari daftar id yang sudah terurut nama, bukan lewat
     * chunkById — chunkById memaksa paging per id sehingga urutan nama hanya
     * berlaku di dalam satu potongan, tidak di keseluruhan berkas.
     */
    private function potongResponden(LingkupResponden $lingkup, callable $tulis, array $relasi): void
    {
        foreach ($lingkup->ids()->chunk(50) as $idPotongan) {
            $responden = User::responden()
                ->whereIn('id', $idPotongan)
                ->with($relasi)
                ->get()
                ->sortBy('nama');

            foreach ($responden as $user) {
                $tulis($user);
            }
        }
    }
}
