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
        $admin = User::factory()->create([
            'email' => 'admin@asawatch.com',
        ]);

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

    public function test_admin_can_view_user_details()
    {
        $admin = User::factory()->create([
            'email' => 'admin@asawatch.com',
        ]);

        $user = User::factory()->create(['nama' => 'John Doe']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    public function test_admin_can_view_user_session_details()
    {
        $admin = User::factory()->create([
            'email' => 'admin@asawatch.com',
        ]);

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
