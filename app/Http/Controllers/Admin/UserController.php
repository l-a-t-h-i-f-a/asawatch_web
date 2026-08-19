<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Sesi;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $query = User::with(['profil', 'perangkat'])->where('email', '!=', 'admin@asawatch.com');

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
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($user->isAdmin()) {
            abort(404);
        }

        $user->load(['profil', 'perangkat' => fn ($query) => $query->latest('terakhir_tersambung')]);

        $sesi = $user->sesi()
            ->with(['sampel', 'hasilDeteksi.itemMakanan'])
            ->orderByDesc('waktu_foto')
            ->get();

        $sampelTerbaru = $sesi
            ->flatMap->sampel
            ->filter(fn ($sampel) => $sampel->detak_jantung || $sampel->gula_darah || $sampel->sistolik)
            ->sortByDesc('created_at')
            ->first();

        return view('admin.responden.detail', [
            'active' => 'users',
            'user' => $user,
            'sesiList' => $sesi,
            'totalSesi' => $user->sesi()->count(),
            'sesiSelesai' => $user->sesi()->where('status', 'selesai')->count(),
            'sampelTerbaru' => $sampelTerbaru,
            'isAdminView' => true,
        ]);
    }

    public function showSession(User $user, Sesi $sesi)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($sesi->user_id !== $user->id) {
            abort(404);
        }

        $sesi->load(['sampel', 'hasilDeteksi.itemMakanan']);

        return view('admin.responden.show', [
            'active' => 'users',
            'sesi' => $sesi,
            'totalSesi' => $user->sesi()->count(),
            'isAdminView' => true,
            'selectedUser' => $user,
        ]);
    }
}
