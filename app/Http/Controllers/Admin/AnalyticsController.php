<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemMakanan;
use App\Models\Sampel;
use App\Models\Sesi;
use App\Support\LingkupResponden;
use Illuminate\Http\Request;

/**
 * Statistik deskriptif responden — seluruhnya, atau satu responden bila
 * dipilih lewat "?responden=". Angka di sini murni ringkasan data tersimpan,
 * bukan rumus turunan milik aplikasi dan bukan kesimpulan medis.

 */
class AnalyticsController extends Controller
{
    /** Skala tampilan bar gula darah: 250 mg/dL dianggap ujung kanan. */
    private const SKALA_GULA_MAKS = 250;

    public function index(Request $request)
    {
        $lingkup = LingkupResponden::dari($request);
        $ids = $lingkup->ids();

        $sampelTerisi = fn () => Sampel::whereHas('sesi', fn ($q) => $q->whereIn('user_id', $ids))
            ->where('status', 'terisi');

        // Satu query agregat untuk semua kolom sekaligus, bukan lima query
        // avg() terpisah atas tabel yang sama.
        $ringkasan = $sampelTerisi()
            ->selectRaw('COUNT(*) as n')
            ->selectRaw('AVG(gula_darah) as rata_gula')
            ->selectRaw('AVG(detak_jantung) as rata_detak')
            ->selectRaw('AVG(sistolik) as rata_sistolik')
            ->selectRaw('AVG(diastolik) as rata_diastolik')
            ->first();

        // Sebaran indeks 0..3 (baseline, t0, +1 jam, +2 jam) — satu query
        // group by, lalu dilengkapi titik yang belum punya data sama sekali.
        // Kolom "index" dipilih lewat query builder (bukan selectRaw) supaya
        // dikutip sesuai driver — di MySQL tanda kutip ganda bukan identifier.
        $perIndex = $sampelTerisi()
            ->select('index')
            ->selectRaw('COUNT(gula_darah) as n')
            ->selectRaw('AVG(gula_darah) as rata_gula')
            ->groupBy('index')
            ->get()
            ->keyBy('index');

        $kurvaPerIndex = collect(range(0, 3))->map(function ($index) use ($perIndex) {
            $rata = (int) round($perIndex[$index]->rata_gula ?? 0);

            return [
                'index' => $index,
                'rata_gula' => $rata,
                'n' => (int) ($perIndex[$index]->n ?? 0),
                // Bar diskalakan terhadap 250 mg/dL — sebelumnya nilai mg/dL
                // dipakai langsung sebagai persen, sehingga 90 mg/dL tampil
                // sebagai bar 90% dan 180 mg/dL sudah mentok.
                'persen_skala' => (int) min(100, round($rata / self::SKALA_GULA_MAKS * 100)),
            ];
        });

        $makananTinggiGula = ItemMakanan::whereHas('sesi', fn ($q) => $q->whereIn('user_id', $ids))
            ->where('gula_total', '>=', 15)
            ->orderByDesc('gula_total')
            ->with('sesi.user:id,nama')
            ->take(5)
            ->get();

        return view('admin.analitik', [
            'active' => 'analitik',
            'lingkup' => $lingkup,
            'totalSesi' => Sesi::whereIn('user_id', $ids)->count(),
            'totalTitikData' => (int) ($ringkasan->n ?? 0),
            'rataRataGula' => (int) round($ringkasan->rata_gula ?? 0),
            'rataRataDetak' => (int) round($ringkasan->rata_detak ?? 0),
            'rataRataSistolik' => (int) round($ringkasan->rata_sistolik ?? 0),
            'rataRataDiastolik' => (int) round($ringkasan->rata_diastolik ?? 0),
            'titikHipertensi' => $sampelTerisi()->where('sistolik', '>=', 140)->count(),
            'titikGulaTinggi' => $sampelTerisi()->where('gula_darah', '>=', 180)->count(),
            'kurvaPerIndex' => $kurvaPerIndex,
            'skalaGulaMaks' => self::SKALA_GULA_MAKS,
            'makananTinggiGula' => $makananTinggiGula,
        ]);
    }
}
