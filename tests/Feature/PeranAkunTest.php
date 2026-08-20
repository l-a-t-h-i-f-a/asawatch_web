<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PeranAkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_akun_baru_berperan_responden_secara_bawaan()
    {
        $user = User::create([
            'nama' => 'Ani',
            'email' => 'ani@contoh.com',
            'password' => 'rahasia123',
        ]);

        $this->assertSame(Peran::RESPONDEN, $user->refresh()->peran);
        $this->assertFalse($user->isAdmin());
    }

    /** Kolom peran menentukan hak akses, jadi tidak boleh ikut mass assignment. */
    public function test_peran_tidak_bisa_diisi_lewat_mass_assignment()
    {
        $user = User::create([
            'nama' => 'Penyusup',
            'email' => 'penyusup@contoh.com',
            'password' => 'rahasia123',
            'peran' => Peran::ADMIN->value,
        ]);

        $this->assertFalse($user->refresh()->isAdmin());
    }

    public function test_email_lama_tidak_lagi_memberi_akses_admin()
    {
        $user = User::factory()->create(['email' => 'admin@asawatch.com']);

        $this->assertFalse($user->isAdmin());
        $this->actingAs($user)->get(route('admin.dashboard'))->assertStatus(403);
    }

    public function test_beberapa_admin_bisa_hidup_berdampingan()
    {
        $satu = User::factory()->admin()->create(['email' => 'a@contoh.com']);
        $dua = User::factory()->admin()->create(['email' => 'b@contoh.com']);

        foreach ([$satu, $dua] as $admin) {
            $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        }

        $this->assertSame(2, User::admin()->count());

        // Admin tidak muncul di daftar responden. Email $satu sendiri tidak
        // diperiksa karena selalu tampil di footer sidebar sebagai akun aktif.
        $this->actingAs($satu)->get(route('admin.users.index'))
            ->assertDontSee('b@contoh.com');

        $this->assertSame(0, User::responden()->count());
    }

    public function test_migrasi_menaikkan_akun_penanda_lama_jadi_admin()
    {
        // Meniru baris yang sudah ada sebelum migrasi: peran dipaksa ke nilai
        // bawaan, lalu langkah backfill migrasi dijalankan ulang.
        $user = User::factory()->create(['email' => 'admin@asawatch.com']);
        DB::table('users')->where('id', $user->id)->update(['peran' => Peran::RESPONDEN->value]);

        DB::table('users')
            ->where('email', 'admin@asawatch.com')
            ->update(['peran' => Peran::ADMIN->value]);

        $this->assertTrue($user->refresh()->isAdmin());
    }

    public function test_perintah_artisan_mengangkat_dan_mencabut_admin()
    {
        User::factory()->admin()->create(['email' => 'utama@contoh.com']);
        $calon = User::factory()->create(['email' => 'calon@contoh.com']);

        $this->artisan('asawatch:admin', ['email' => 'calon@contoh.com'])
            ->assertExitCode(0);
        $this->assertTrue($calon->refresh()->isAdmin());

        $this->artisan('asawatch:admin', ['email' => 'calon@contoh.com', '--cabut' => true])
            ->assertExitCode(0);
        $this->assertFalse($calon->refresh()->isAdmin());
    }

    public function test_perintah_artisan_menolak_mencabut_admin_terakhir()
    {
        $satunya = User::factory()->admin()->create(['email' => 'satu@contoh.com']);

        $this->artisan('asawatch:admin', ['email' => 'satu@contoh.com', '--cabut' => true])
            ->assertExitCode(1);

        $this->assertTrue($satunya->refresh()->isAdmin());
    }

    public function test_perintah_artisan_menolak_email_tak_dikenal()
    {
        $this->artisan('asawatch:admin', ['email' => 'entah@contoh.com'])
            ->assertExitCode(1);
    }
}
