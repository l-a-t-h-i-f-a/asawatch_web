<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sesi;
use Illuminate\Http\Request;

/**
 * Kotak cari di header. Sebelumnya hanya hiasan: berlabel "Cari ID sesi"
 * tapi tidak terhubung ke mana pun.
 */
class PencarianController extends Controller
{
    public function __invoke(Request $request)
    {
        $kueri = trim((string) $request->query('q'));

        if ($kueri === '') {
            return redirect()->route('admin.users.index');
        }

        // ID sesi berupa UUID — kalau cocok dan sesinya ada, langsung buka
        // halaman sesi itu daripada menampilkan daftar responden kosong.
        $sesi = Sesi::with('user')->whereKey($kueri)->first();

        if ($sesi && $sesi->user && ! $sesi->user->isAdmin()) {
            return redirect()->route('admin.users.session.show', [$sesi->user, $sesi]);
        }

        return redirect()->route('admin.users.index', ['search' => $kueri]);
    }
}
