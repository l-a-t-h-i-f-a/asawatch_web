<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perangkat;
use App\Models\Sampel;
use App\Models\Sesi;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Ringkasan lintas responden. Semua angka di sini agregat sistem — panel
 * web tidak lagi menampilkan data milik akun admin sendiri.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $respondenIds = User::responden()->pluck('id');

        $sesiQuery = fn () => Sesi::whereIn('user_id', $respondenIds);
        $sampelTerisi = fn () => Sampel::whereHas('sesi', fn ($q) => $q->whereIn('user_id', $respondenIds))
            ->where('status', 'terisi');

        // "Hari ini" mengacu ke waktu foto sesi, bukan sampel.created_at —
        // created_at adalah waktu baris tersinkron dari ponsel, yang bisa
        // terjadi berhari-hari setelah pengukurannya.
        $rataRataGula = (int) round(
            $sampelTerisi()
                ->whereHas('sesi', fn ($q) => $q->whereDate('waktu_foto', today()))
                ->avg('gula_darah') ?? 0
        );

        // Ambang mentah untuk triase tampilan — bukan rumus turunan seperti
        // "lonjakan puncak" milik aplikasi (bagian 2 dokumen API, aturan #1).
        // Join eksplisit (bukan whereHas) supaya bisa diurutkan menurut waktu
        // pengukuran; kolom dikualifikasi karena "status" ada di kedua tabel.
        $sampelPerluPerhatian = Sampel::query()
            ->join('sesi', 'sesi.id', '=', 'sampel.sesi_id')
            ->whereNull('sesi.deleted_at')
            ->whereIn('sesi.user_id', $respondenIds)
            ->where('sampel.status', 'terisi')
            ->where(function ($q) {
                $q->where('sampel.gula_darah', '>=', 180)
                    ->orWhere('sampel.sistolik', '>=', 140)
                    ->orWhere('sampel.detak_jantung', '>=', 100);
            })
            ->orderByDesc('sesi.waktu_foto')
            ->orderBy('sampel.index')
            ->select('sampel.*')
            ->with('sesi.user:id,nama')
            ->take(6)
            ->get();

        $respondenTeraktif = User::responden()
            ->withCount([
                'sesi',
                'sesi as sesi_selesai_count' => fn ($q) => $q->where('status', 'selesai'),
            ])
            ->orderByDesc('sesi_count')
            ->orderBy('nama')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'active' => 'dashboard',
            'totalResponden' => $respondenIds->count(),
            'totalSesi' => $sesiQuery()->count(),
            'sesiSelesai' => $sesiQuery()->where('status', 'selesai')->count(),
            'rataRataGula' => $rataRataGula,
            'sampelPerluPerhatian' => $sampelPerluPerhatian,
            'perangkatTerdaftar' => Perangkat::whereIn('user_id', $respondenIds)->count(),
            'respondenTeraktif' => $respondenTeraktif,
        ]);
    }
}
