<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Versi web dari GET /api/v1/akun/ekspor (bagian 9 dokumen API) — hak
 * pengguna untuk mengunduh seluruh datanya sendiri, bukan fitur tambahan.
 */
class ExportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('admin.ekspor.index', [
            'active' => 'ekspor',
            'totalSesi' => $user->sesi()->count(),
            'totalKalibrasi' => $user->kalibrasi()->count(),
            'totalPerangkat' => $user->perangkat()->count(),
        ]);
    }

    public function downloadJson(Request $request)
    {
        $user = $request->user()->load([
            'profil',
            'sesi.sampel',
            'sesi.hasilDeteksi.itemMakanan',
            'kalibrasi',
            'perangkat',
        ]);

        $data = [
            'profil' => $user->profil,
            'sesi' => $user->sesi,
            'kalibrasi' => $user->kalibrasi,
            'perangkat' => $user->perangkat,
        ];

        $nama = 'asawatch_data_'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(
            fn () => print(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            $nama,
            ['Content-Type' => 'application/json'],
        );
    }

    public function downloadCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $nama = 'asawatch_sampel_'.now()->format('Y-m-d').'.csv';

        $sampel = $user->sesi()->with('sampel')->get()->flatMap(fn ($sesi) => $sesi->sampel->map(fn ($s) => [
            'sesi_id' => $sesi->id,
            'waktu_foto' => $sesi->waktu_foto,
            'index' => $s->index,
            'detik_relatif_t0' => $s->detik_relatif_t0,
            'status' => $s->status,
            'gula_darah' => $s->gula_darah,
            'detak_jantung' => $s->detak_jantung,
            'sistolik' => $s->sistolik,
            'diastolik' => $s->diastolik,
            'spo2' => $s->spo2,
        ]));

        return response()->streamDownload(function () use ($sampel) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['sesi_id', 'waktu_foto', 'index', 'detik_relatif_t0', 'status', 'gula_darah', 'detak_jantung', 'sistolik', 'diastolik', 'spo2']);

            foreach ($sampel as $baris) {
                fputcsv($out, $baris);
            }

            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv']);
    }
}
