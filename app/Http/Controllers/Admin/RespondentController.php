<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sesi;
use Illuminate\Http\Request;

/**
 * Nama kelas & route dipertahankan (admin.responden.*) supaya template
 * tampilan lama tetap terpakai apa adanya — tapi datanya sekarang riwayat
 * sesi makan milik satu pengguna yang login, bukan daftar multi-pasien
 * (dokumen API menegaskan isolasi data ketat per akun, bagian 9).
 */
class RespondentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->sesi()->with(['sampel', 'hasilDeteksi']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $sesi = $query->orderByDesc('waktu_foto')->paginate(10)->withQueryString();

        return view('admin.responden.index', [
            'active' => 'responden',
            'sesiList' => $sesi,
            'totalSesi' => $user->sesi()->count(),
        ]);
    }

    public function show(Sesi $sesi)
    {
        $this->authorize('view', $sesi);

        $sesi->load(['sampel', 'hasilDeteksi.itemMakanan']);

        return view('admin.responden.show', [
            'active' => 'detail',
            'sesi' => $sesi,
            'totalSesi' => $sesi->user->sesi()->count(),
        ]);
    }

    public function latest(Request $request)
    {
        $user = $request->user();
        $user->load(['profil', 'perangkat' => fn ($query) => $query->latest('terakhir_tersambung')]);

        $sesi = $user->sesi()
            ->with(['sampel', 'hasilDeteksi.itemMakanan'])
            ->orderByDesc('waktu_foto')
            ->take(8)
            ->get();

        $sampelTerbaru = $sesi
            ->flatMap->sampel
            ->filter(fn ($sampel) => $sampel->detak_jantung || $sampel->gula_darah || $sampel->sistolik)
            ->sortByDesc('created_at')
            ->first();

        return view('admin.responden.detail', [
            'active' => 'detail',
            'user' => $user,
            'sesiList' => $sesi,
            'totalSesi' => $user->sesi()->count(),
            'sesiSelesai' => $user->sesi()->where('status', 'selesai')->count(),
            'sampelTerbaru' => $sampelTerbaru,
        ]);
    }

    public function latestSession(Request $request)
    {
        $sesi = $request->user()->sesi()->orderByDesc('waktu_foto')->first();

        if (! $sesi) {
            return redirect()->route('admin.responden.index');
        }

        return redirect()->route('admin.responden.show', $sesi);
    }
}
