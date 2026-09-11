<?php

namespace Tests\Feature;

use App\Models\Sampel;
use App\Models\Sesi;
use App\Models\User;
use App\Services\Ekspor\FormatEkspor;
use App\Services\Ekspor\SkemaEkspor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/**
 * Saringan halaman ekspor: yang tampil di ringkasan harus persis yang turun
 * di berkas, dan berkas xlsx harus benar-benar berisi seluruh lembar.
 */
class EksporTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /** Satu responden dengan satu sesi berisi satu titik pengukuran. */
    private function responden(string $nama, array $atributSesi = []): User
    {
        $user = User::factory()->create(['nama' => $nama]);
        $sesi = Sesi::factory()->create(['user_id' => $user->id] + $atributSesi);

        Sampel::create([
            'sesi_id' => $sesi->id,
            'index' => 0,
            'detik_relatif_t0' => 0,
            'status' => 'terisi',
            'gula_darah' => 110,
        ]);

        return $user;
    }

    public function test_ringkasan_menghitung_sesi_dalam_lingkup()
    {
        $this->responden('Ani', ['waktu_foto' => now()->subDays(2)]);
        $this->responden('Budi', ['waktu_foto' => now()->subDays(2)]);

        $ringkasan = $this->actingAs($this->admin())
            ->get(route('admin.ekspor.index'))
            ->assertStatus(200)
            ->viewData('ringkasan');

        $this->assertSame(2, $ringkasan->responden);
        $this->assertSame(2, $ringkasan->sesi);
        $this->assertSame(2, $ringkasan->sampelTerisi);
        $this->assertFalse($ringkasan->kosong());
    }

    public function test_rentang_tanggal_menyingkirkan_sesi_di_luar_periode()
    {
        $this->responden('Ani', ['waktu_foto' => now()->subDays(2)]);
        $this->responden('Budi', ['waktu_foto' => now()->subDays(100)]);

        $response = $this->actingAs($this->admin())->get(route('admin.ekspor.index', [
            'dari' => now()->subDays(29)->format('Y-m-d'),
            'sampai' => now()->format('Y-m-d'),
        ]));

        $this->assertSame(1, $response->viewData('ringkasan')->sesi);

        $csv = $this->actingAs($this->admin())->get(route('admin.ekspor.csv', [
            'dari' => now()->subDays(29)->format('Y-m-d'),
            'sampai' => now()->format('Y-m-d'),
        ]));

        $isi = $csv->streamedContent();
        $this->assertStringContainsString('Ani', $isi);
        $this->assertStringNotContainsString('Budi', $isi);
    }

    public function test_sesi_uji_bisa_dikecualikan()
    {
        $this->responden('Ani');
        $this->responden('Budi', ['sesi_uji' => true]);

        $semua = $this->actingAs($this->admin())->get(route('admin.ekspor.index'));
        $this->assertSame(2, $semua->viewData('ringkasan')->sesi);
        $this->assertSame(1, $semua->viewData('ringkasan')->sesiUji);

        $bersih = $this->actingAs($this->admin())->get(route('admin.ekspor.index', ['sesi_uji' => '0']));
        $this->assertSame(1, $bersih->viewData('ringkasan')->sesi);
        $this->assertSame(0, $bersih->viewData('ringkasan')->sesiUji);
    }

    public function test_sesi_terhapus_tidak_pernah_ikut()
    {
        $this->responden('Ani');
        $budi = $this->responden('Budi');
        $budi->sesi()->first()->delete();

        $response = $this->actingAs($this->admin())->get(route('admin.ekspor.index', ['terhapus' => '1']));
        $this->assertSame(1, $response->viewData('ringkasan')->sesi);

        $csv = $this->actingAs($this->admin())->get(route('admin.ekspor.csv'))->streamedContent();
        $this->assertStringNotContainsString('Budi', $csv);
    }

    public function test_csv_diawali_bom_dan_pemisah_bisa_dipilih()
    {
        $this->responden('Ani');

        $koma = $this->actingAs($this->admin())->get(route('admin.ekspor.csv'))->streamedContent();
        $this->assertStringStartsWith("\u{FEFF}", $koma);
        $this->assertStringContainsString('user_id,nama,email', $koma);

        $titikKoma = $this->actingAs($this->admin())
            ->get(route('admin.ekspor.csv', ['pemisah' => 'titik_koma']))
            ->streamedContent();
        $this->assertStringContainsString('user_id;nama;email', $titikKoma);
    }

    public function test_xlsx_berisi_seluruh_lembar_dan_kamus_data()
    {
        $this->responden('Ani');

        $response = $this->actingAs($this->admin())->get(route('admin.ekspor.xlsx'));
        $response->assertStatus(200);

        $berkas = $response->getFile()->getPathname();
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($berkas) === true, 'Berkas xlsx harus berupa arsip yang bisa dibuka');

        preg_match_all('/<sheet name="([^"]+)"/', $zip->getFromName('xl/workbook.xml'), $cocok);
        $zip->close();

        foreach (array_keys(SkemaEkspor::lembar()) as $lembar) {
            $this->assertContains($lembar, $cocok[1], "Lembar {$lembar} harus ada di berkas xlsx");
        }

        // Dua lapis: panduan + lembar ramah-baca di depan, kamus di akhir.
        $this->assertSame('Baca Ini Dulu', $cocok[1][0]);
        $this->assertSame(SkemaEkspor::LEMBAR_RINGKASAN, $cocok[1][1]);
        $this->assertSame('Kamus Data', end($cocok[1]));
    }

    public function test_ekspor_menghormati_filter_responden_di_xlsx()
    {
        $ani = $this->responden('Ani');
        $this->responden('Budi');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.ekspor.index', ['responden' => $ani->id]));

        $this->assertSame(1, $response->viewData('ringkasan')->sesi);
        $this->assertSame($ani->id, $response->viewData('saringan')->lingkup->user->id);
    }

    public function test_satu_tombol_unduh_mengikuti_format_yang_dipilih()
    {
        $this->responden('Ani');

        $csv = $this->actingAs($this->admin())->get(route('admin.ekspor.unduh', ['format' => 'csv']));
        $csv->assertStatus(200);
        $this->assertStringContainsString('user_id,nama,email', $csv->streamedContent());

        $json = $this->actingAs($this->admin())->get(route('admin.ekspor.unduh', ['format' => 'json']));
        $this->assertIsArray(json_decode($json->streamedContent(), true));
    }

    public function test_format_tak_dikenal_jatuh_ke_excel_bukan_galat()
    {
        $this->responden('Ani');

        $response = $this->actingAs($this->admin())->get(route('admin.ekspor.unduh', ['format' => 'pdf']));

        $response->assertStatus(200);
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));

        // Halaman juga tidak boleh pecah, cukup kembali ke pilihan bawaan.
        $this->actingAs($this->admin())
            ->get(route('admin.ekspor.index', ['format' => 'pdf']))
            ->assertStatus(200)
            ->assertViewHas('format', FormatEkspor::XLSX);
    }

    public function test_saringan_tanpa_hasil_menandai_ringkasan_kosong()
    {
        $this->responden('Ani', ['waktu_foto' => now()->subDays(100)]);

        $response = $this->actingAs($this->admin())->get(route('admin.ekspor.index', [
            'dari' => now()->subDays(7)->format('Y-m-d'),
        ]));

        $this->assertTrue($response->viewData('ringkasan')->kosong());
        $response->assertSee('Tidak ada sesi yang cocok dengan saringan ini');
    }
}
