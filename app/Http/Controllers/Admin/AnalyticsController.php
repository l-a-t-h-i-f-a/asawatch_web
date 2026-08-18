<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemMakanan;
use App\Models\Sampel;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $sampelQuery = Sampel::whereHas('sesi', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'terisi');

        $totalTitikData = $sampelQuery->clone()->count();
        $rataRataGula = round($sampelQuery->clone()->avg('gula_darah') ?? 0);
        $rataRataDetak = round($sampelQuery->clone()->avg('detak_jantung') ?? 0);
        $rataRataSistolik = round($sampelQuery->clone()->avg('sistolik') ?? 0);
        $rataRataDiastolik = round($sampelQuery->clone()->avg('diastolik') ?? 0);

        $sesiHipertensiKandidat = $sampelQuery->clone()->where('sistolik', '>=', 140)->count();

        // Sebaran indeks 0..3 (baseline, t0, +1 jam, +2 jam) — rata-rata gula
        // darah per titik waktu, murni deskriptif dari data tersimpan.
        $kurvaPerIndex = collect(range(0, 3))->map(function ($index) use ($sampelQuery) {
            return [
                'index' => $index,
                'rata_gula' => round($sampelQuery->clone()->where('index', $index)->avg('gula_darah') ?? 0),
                'n' => $sampelQuery->clone()->where('index', $index)->count(),
            ];
        });

        $makananTinggiGula = ItemMakanan::whereHas('sesi', fn ($q) => $q->where('user_id', $user->id))
            ->where('gula_total', '>=', 15)
            ->orderByDesc('gula_total')
            ->take(5)
            ->get();

        return view('admin.analitik', [
            'active' => 'analitik',
            'totalTitikData' => $totalTitikData,
            'rataRataGula' => $rataRataGula,
            'rataRataDetak' => $rataRataDetak,
            'rataRataSistolik' => $rataRataSistolik,
            'rataRataDiastolik' => $rataRataDiastolik,
            'sesiHipertensiKandidat' => $sesiHipertensiKandidat,
            'kurvaPerIndex' => $kurvaPerIndex,
            'makananTinggiGula' => $makananTinggiGula,
        ]);
    }
}
