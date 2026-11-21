<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingAndErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
    }

    public function test_landing_page_renders_clean_pst_bps_portal(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Pelayanan Statistik Terpadu (PST)');
        $response->assertSee('Badan Pusat Statistik Kota Tasikmalaya');
        $response->assertSee('Anjungan Kiosk Tiket');
        $response->assertSee('Layar Monitor TV');
        $response->assertSee('Meja Kerja Pelayanan');
        $response->assertSee('Dashboard & Rekap', false);
        $response->assertSee('UU No. 27 Tahun 2022');
    }

    public function test_not_found_page_renders_indonesian_404_view(): void
    {
        $response = $this->get('/halaman-yang-tidak-pernah-ada-123');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('Kembali ke Beranda Utama');
    }

    public function test_unauthorized_access_renders_indonesian_403_view(): void
    {
        $petugas = User::where('role', 'petugas')->first();

        // Petugas mencoba mengakses rute admin
        $response = $this->actingAs($petugas)->get('/admin/dashboard');

        $response->assertStatus(403);
        $response->assertSee('403');
        $response->assertSee('Akses Ditolak');
        $response->assertSee('Kembali ke Beranda Utama');
    }
}
