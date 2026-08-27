<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sampel;
use App\Models\Sesi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::responden()
            ->with([
                'profil',
                // Diurutkan supaya perangkat->first() di tabel benar-benar
                // perangkat yang terakhir tersambung, bukan urutan acak.
                'perangkat' => fn ($q) => $q->orderByDesc('terakhir_tersambung'),
            ])
            ->withCount(['sesi', 'sesi as sesi_selesai_count' => fn ($q) => $q->where('status', 'selesai')]);

        if ($search = $request->query('search')) {
            $query->where(fn ($q) => $q->where('nama', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $users = $query->orderBy('nama')->paginate(10)->withQueryString();

        return view('admin.users.index', [
            'active' => 'users',
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function show(User $user)
    {
        abort_if($user->isAdmin(), 404);

        $user->load(['profil', 'perangkat' => fn ($query) => $query->latest('terakhir_tersambung')])
            ->loadCount(['sesi', 'sesi as sesi_selesai_count' => fn ($q) => $q->where('status', 'selesai')]);

        // Hanya sesi yang benar-benar ditampilkan yang dimuat — sebelumnya
        // seluruh riwayat responden (beserta sampel dan item makanannya)
        // ditarik ke memori hanya untuk mengambil 8 teratas dan menghitung.
        $sesiList = $user->sesi()
            ->with('sampel')
            ->orderByDesc('waktu_foto')
            ->take(8)
            ->get();

        // Pengukuran terakhir dicari lewat query, bukan dari 8 sesi di atas —
        // sesi terbaru bisa saja belum punya sampel terisi sama sekali.
        $sampelTerbaru = Sampel::query()
            ->join('sesi', 'sesi.id', '=', 'sampel.sesi_id')
            ->whereNull('sesi.deleted_at')
            ->where('sesi.user_id', $user->id)
            ->where('sampel.status', 'terisi')
            ->where(function ($q) {
                $q->whereNotNull('sampel.detak_jantung')
                    ->orWhereNotNull('sampel.gula_darah')
                    ->orWhereNotNull('sampel.sistolik');
            })
            ->orderByDesc('sesi.waktu_foto')
            ->orderByDesc('sampel.index')
            ->select('sampel.*')
            ->first();

        return view('admin.users.show', [
            'active' => 'users',
            'user' => $user,
            'sesiList' => $sesiList,
            'totalSesi' => $user->sesi_count,
            'sesiSelesai' => $user->sesi_selesai_count,
            'sampelTerbaru' => $sampelTerbaru,
        ]);
    }

    public function showSession(User $user, Sesi $sesi)
    {
        $this->pastikanSesiMilikResponden($user, $sesi);

        $sesi->load(['sampel', 'hasilDeteksi.itemMakanan']);

        return view('admin.users.session', [
            'active' => 'users',
            'sesi' => $sesi,
            'selectedUser' => $user,
        ]);
    }

    /**
     * Foto makanan tetap di disk privat — tidak ada URL publik ke sana
     * (aturan yang sama dengan Api\V1\FotoController). Di web, penjaganya
     * adalah sesi login + middleware 'admin' pada grup rutenya.
     */
    public function foto(User $user, Sesi $sesi)
    {
        $this->pastikanSesiMilikResponden($user, $sesi);

        if (! $sesi->foto_disk_path || ! Storage::disk('local')->exists($sesi->foto_disk_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($sesi->foto_disk_path);
    }

    private function pastikanSesiMilikResponden(User $user, Sesi $sesi): void
    {
        abort_if($user->isAdmin(), 404);

        if ($sesi->user_id !== $user->id) {
            abort(404);
        }
    }
}
