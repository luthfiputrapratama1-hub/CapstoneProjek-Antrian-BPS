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

    public function test_portal_auth_and_error_pages_use_offline_vite_without_emojis_or_cdn(): void
    {
        $landing = $this->get('/')->assertStatus(200);
        $landing->assertSee('Layanan Publik &amp; Ruang Tunggu', false);
        $landing->assertSee('Operasional Staf &amp; Pimpinan', false);
        $landing->assertDontSee('cdn.tailwindcss.com');
        $landing->assertDontSee('🏛️');
        $landing->assertDontSee('🎫');

        $login = $this->get('/login')->assertStatus(200);
        $login->assertSee('autocomplete="username"', false);
        $login->assertSee('autocomplete="current-password"', false);
        $login->assertSee('data-password-toggle="password"', false);
        $login->assertDontSee('cdn.tailwindcss.com');

        $this->actingAs(User::where('role', 'petugas')->first());
        $changePwd = $this->get('/password/change')->assertStatus(200);
        $changePwd->assertSee('autocomplete="new-password"', false);
        $changePwd->assertDontSee('cdn.tailwindcss.com');
        $changePwd->assertDontSee('🔒');

        $notFound = $this->get('/halaman-yang-tidak-pernah-ada-123')->assertStatus(404);
        $notFound->assertDontSee('cdn.tailwindcss.com');
        $notFound->assertDontSee('🔍');

        $forbidden = $this->get('/admin/dashboard')->assertStatus(403);
        $forbidden->assertDontSee('cdn.tailwindcss.com');
        $forbidden->assertDontSee('⛔');
    }

    public function test_419_and_500_error_views_render_cleanly_without_cdn_or_emojis(): void
    {
        $view419 = view('errors.419')->render();
        $this->assertStringContainsString('Sesi Halaman Kedaluwarsa', $view419);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $view419);
        $this->assertStringNotContainsString('⏳', $view419);

        $view500 = view('errors.500')->render();
        $this->assertStringContainsString('Terjadi Kesalahan Sistem', $view500);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $view500);
        $this->assertStringNotContainsString('⚠️', $view500);
    }
}
