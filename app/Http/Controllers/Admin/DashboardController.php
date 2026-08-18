<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sampel;
use App\Models\Sesi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalSesi = $user->sesi()->count();
        $sesiSelesai = $user->sesi()->where('status', 'selesai')->count();

        $sampelHariIni = Sampel::whereHas('sesi', fn ($q) => $q->where('user_id', $user->id))
            ->whereDate('created_at', today())
            ->where('status', 'terisi');

        $rataRataGula = (int) round($sampelHariIni->clone()->avg('gula_darah') ?? 0);

        // Ambang mentah untuk triase tampilan — bukan rumus turunan seperti
        // "lonjakan puncak" milik aplikasi (bagian 2 dokumen API, aturan #1).
        $sampelPerluPerhatian = Sampel::whereHas('sesi', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'terisi')
            ->where(function ($q) {
                $q->where('gula_darah', '>=', 180)
                    ->orWhere('sistolik', '>=', 140)
                    ->orWhere('detak_jantung', '>=', 100);
            })
            ->with('sesi')
            ->latest('created_at')
            ->take(4)
            ->get();

        $perangkatTersambung = $user->perangkat()->count();

        return view('admin.dashboard', [
            'active' => 'dashboard',
            'totalSesi' => $totalSesi,
            'sesiSelesai' => $sesiSelesai,
            'rataRataGula' => $rataRataGula,
            'sampelPerluPerhatian' => $sampelPerluPerhatian,
            'perangkatTersambung' => $perangkatTersambung,
            'kalibrasiTerakhir' => $user->kalibrasi()->latest('waktu')->first(),
        ]);
    }
}
