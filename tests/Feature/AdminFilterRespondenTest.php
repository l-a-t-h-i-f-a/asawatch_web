<?php

namespace Tests\Feature;

use App\Models\Sampel;
use App\Models\Sesi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFilterRespondenTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function admin(): User
    {
        return $this->admin ??= User::factory()->admin()->create();
    }

    /** Satu responden dengan satu sesi berisi satu titik gula darah. */
    private function respondenDenganGula(string $nama, int $gula): User
    {
        $user = User::factory()->create(['nama' => $nama]);
        $sesi = Sesi::factory()->create(['user_id' => $user->id]);

        Sampel::create([
            'sesi_id' => $sesi->id,
            'index' => 0,
            'detik_relatif_t0' => 0,
            'status' => 'terisi',
            'gula_darah' => $gula,
        ]);

        return $user;
    }

    public function test_analitik_tanpa_filter_menggabungkan_semua_responden()
    {
        $this->respondenDenganGula('Ani', 100);
        $this->respondenDenganGula('Budi', 200);

        $response = $this->actingAs($this->admin())->get(route('admin.analitik'));

        $response->assertStatus(200);
        $this->assertSame(2, $response->viewData('totalTitikData'));
        $this->assertSame(150, $response->viewData('rataRataGula'));
        $this->assertTrue($response->viewData('lingkup')->semua());
    }

    public function test_analitik_bisa_difilter_ke_satu_responden()
    {
        $ani = $this->respondenDenganGula('Ani', 100);
        $this->respondenDenganGula('Budi', 200);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.analitik', ['responden' => $ani->id]));

        $response->assertStatus(200);
        $this->assertSame(1, $response->viewData('totalTitikData'));
        $this->assertSame(100, $response->viewData('rataRataGula'));
        $this->assertSame($ani->id, $response->viewData('lingkup')->user->id);
    }

    public function test_filter_ke_akun_admin_atau_id_asal_diperlakukan_sebagai_semua()
    {
        $admin = $this->admin();
        $this->respondenDenganGula('Ani', 100);

        foreach ([$admin->id, 999999, 'bukan-id'] as $nilai) {
            $response = $this->actingAs($admin)
                ->get(route('admin.analitik', ['responden' => $nilai]));

            $response->assertStatus(200);
            $this->assertTrue($response->viewData('lingkup')->semua(), "gagal untuk: {$nilai}");
        }
    }

    public function test_ekspor_menghormati_filter_responden()
    {
        $ani = $this->respondenDenganGula('Ani', 100);
        $this->respondenDenganGula('Budi', 200);

        $csv = $this->actingAs($this->admin())
            ->get(route('admin.ekspor.csv', ['responden' => $ani->id]));

        $csv->assertStatus(200);
        $isi = $csv->streamedContent();
        $this->assertStringContainsString('Ani', $isi);
        $this->assertStringNotContainsString('Budi', $isi);

        $json = $this->actingAs($this->admin())
            ->get(route('admin.ekspor.json', ['responden' => $ani->id]));

        $json->assertStatus(200);
        $data = json_decode($json->streamedContent(), true);
        $this->assertIsArray($data, 'JSON ekspor harus valid');
        $this->assertCount(1, $data['responden']);
        $this->assertSame('Ani', $data['responden'][0]['nama']);
    }

    public function test_ekspor_json_semua_responden_tetap_json_valid()
    {
        $this->respondenDenganGula('Ani', 100);
        $this->respondenDenganGula('Budi', 200);

        $json = $this->actingAs($this->admin())->get(route('admin.ekspor.json'));

        $data = json_decode($json->streamedContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(2, $data['responden']);
        $this->assertSame(['Ani', 'Budi'], array_column($data['responden'], 'nama'));
    }

    public function test_pencarian_header_membuka_sesi_bila_id_cocok()
    {
        $user = User::factory()->create(['nama' => 'Ani']);
        $sesi = Sesi::factory()->create(['user_id' => $user->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.cari', ['q' => $sesi->id]))
            ->assertRedirect(route('admin.users.session.show', [$user, $sesi]));

        $this->actingAs($this->admin())
            ->get(route('admin.cari', ['q' => 'Ani']))
            ->assertRedirect(route('admin.users.index', ['search' => 'Ani']));
    }

    public function test_login_dibatasi_setelah_percobaan_gagal_berulang()
    {
        $admin = User::factory()->admin()->create(['password' => 'benar123']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.submit'), [
                'email' => $admin->email,
                'password' => 'salah',
            ]);
        }

        // Percobaan ke-6 ditolak walau sandinya benar.
        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'benar123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
