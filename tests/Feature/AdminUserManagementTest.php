<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Sesi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_list()
    {
        $admin = User::factory()->admin()->create();

        $user1 = User::factory()->create(['nama' => 'User One']);
        $user2 = User::factory()->create(['nama' => 'User Two']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User One');
        $response->assertSee('User Two');
    }

    public function test_non_admin_cannot_view_users_list()
    {
        $user = User::factory()->create([
            'email' => 'user@asawatch.com',
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /** Portal web tidak lagi punya halaman "data saya" untuk akun responden. */
    public function test_non_admin_cannot_access_any_admin_page()
    {
        $user = User::factory()->create(['email' => 'user@asawatch.com']);

        foreach (['admin.dashboard', 'admin.analitik', 'admin.ekspor.index'] as $rute) {
            $this->actingAs($user)->get(route($rute))->assertStatus(403);
        }
    }

    public function test_non_admin_cannot_log_in_to_web_portal()
    {
        User::factory()->create([
            'email' => 'user@asawatch.com',
            'password' => 'rahasia123',
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'user@asawatch.com',
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_log_in_to_web_portal()
    {
        // Email sengaja bukan admin@asawatch.com: yang menentukan akses
        // sekarang kolom peran, bukan alamat emailnya.
        User::factory()->admin()->create([
            'email' => 'pengelola@asawatch.com',
            'password' => 'rahasia123',
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'pengelola@asawatch.com',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_dashboard_shows_cross_user_summary()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['nama' => 'Rina Responden']);
        Sesi::factory()->create([
            'user_id' => $user->id,
            'waktu_foto' => now(),
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Responden');
        $response->assertSee('Rina Responden');
        $response->assertDontSee('Analitik Saya');
    }

    public function test_analitik_and_ekspor_are_scoped_to_all_respondents()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['nama' => 'Budi Responden']);
        Sesi::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin)->get(route('admin.analitik'))
            ->assertStatus(200)
            ->assertSee('Analitik')
            ->assertDontSee('Analitik Saya');

        $this->actingAs($admin)->get(route('admin.ekspor.index'))
            ->assertStatus(200)
            ->assertSee('Responden tercakup')
            ->assertDontSee('Ekspor Data Saya');
    }

    public function test_ekspor_downloads_cover_every_respondent()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['nama' => 'Citra Responden']);
        Sesi::factory()->create(['user_id' => $user->id]);

        $json = $this->actingAs($admin)->get(route('admin.ekspor.json'));
        $json->assertStatus(200);
        $this->assertStringContainsString('Citra Responden', $json->streamedContent());

        $csv = $this->actingAs($admin)->get(route('admin.ekspor.csv'));
        $csv->assertStatus(200);
        $this->assertStringContainsString('user_id,nama,email', $csv->streamedContent());
    }

    public function test_removed_personal_routes_no_longer_exist()
    {
        $admin = User::factory()->admin()->create();

        foreach (['/admin/responden', '/admin/responden/detail', '/admin/responden/sesi-terbaru'] as $path) {
            $this->actingAs($admin)->get($path)->assertStatus(404);
        }
    }

    public function test_admin_can_view_user_details()
    {
        $admin = User::factory()->admin()->create();

        $user = User::factory()->create(['nama' => 'John Doe']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    public function test_admin_can_view_user_session_details()
    {
        $admin = User::factory()->admin()->create();

        $user = User::factory()->create();
        $sesi = Sesi::factory()->create([
            'user_id' => $user->id,
            'waktu_foto' => now(),
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.session.show', [$user, $sesi]));

        $response->assertStatus(200);
        $response->assertSee('Selesai');
    }
}
