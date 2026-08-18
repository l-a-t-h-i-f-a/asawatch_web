<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Dijadwalkan 7 hari setelah DELETE /auth/akun (bagian 4 dokumen API), yang
 * langsung mencabut akses lewat soft delete. Job ini menghapus permanen.
 *
 * users.id di-cascade ON DELETE ke profil/sesi/kalibrasi/perangkat/
 * target_harian di level database, dan sesi.id di-cascade lebih jauh ke
 * sampel/hasil_deteksi/item_makanan/pekerjaan_analisis — jadi satu
 * forceDelete() pada User sudah cukup untuk membereskan semua baris terkait.
 * Yang tidak ikut ter-cascade: token Sanctum (relasi polimorfik, bukan FK
 * sungguhan) dan file foto di storage — keduanya dibereskan manual di sini.
 */
class HapusAkunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $userId) {}

    public function handle(): void
    {
        $user = User::withTrashed()->find($this->userId);

        if (! $user || ! $user->trashed()) {
            // Sudah tidak ada, atau sempat dipulihkan sebelum 7 hari berlalu.
            return;
        }

        $pathFoto = $user->sesi()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->pluck('foto_disk_path')
            ->filter();

        $user->tokens()->delete();
        $user->forceDelete();

        foreach ($pathFoto as $path) {
            Storage::disk('local')->delete($path);
        }
    }
}
