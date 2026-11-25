<?php

namespace Tests\Feature;

use App\Models\GuestBook;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
        $this->admin = User::where('role', 'admin')->first();
    }

    public function test_admin_can_access_dashboard_with_month_year_filter_and_mom(): void
    {
        $service = Service::first();

        // 1 kunjungan bulan lalu
        $lastMonth = Carbon::now()->subMonth();
        $qOld = Queue::create([
            'queue_date' => $lastMonth->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $service->id,
            'status' => 'completed',
            'wait_duration_seconds' => 120,
            'serve_duration_seconds' => 300,
            'created_at' => $lastMonth->copy()->setTime(9, 0),
            'called_at' => $lastMonth->copy()->setTime(9, 2),
            'served_at' => $lastMonth->copy()->setTime(9, 2),
            'completed_at' => $lastMonth->copy()->setTime(9, 7),
        ]);
        GuestBook::create([
            'queue_id' => $qOld->id,
            'full_name' => 'Tamu Bulan Lalu',
            'phone_number' => '081234567890',
            'institution_name' => 'Universitas Siliwangi',
            'institution_type' => 'pendidikan',
            'data_needs_description' => 'Data PDRB',
        ]);

        // 2 kunjungan bulan ini
        $now = Carbon::now();
        for ($i = 1; $i <= 2; $i++) {
            $qNew = Queue::create([
                'queue_date' => $now->toDateString(),
                'ticket_code' => 'A-00' . ($i + 1),
                'ticket_number' => $i + 1,
                'service_id' => $service->id,
                'status' => 'completed',
                'wait_duration_seconds' => 60,
                'serve_duration_seconds' => 180,
                'created_at' => $now->copy()->setTime(10, 0),
                'called_at' => $now->copy()->setTime(10, 1),
                'served_at' => $now->copy()->setTime(10, 1),
                'completed_at' => $now->copy()->setTime(10, 4),
            ]);
            GuestBook::create([
                'queue_id' => $qNew->id,
                'full_name' => 'Tamu Bulan Ini ' . $i,
                'phone_number' => '08129999888' . $i,
                'institution_name' => 'Bappeda Jawa Barat',
                'institution_type' => 'pemerintah',
                'data_needs_description' => 'Data Kemiskinan',
            ]);
        }

        $this->actingAs($this->admin);

        // Akses dashboard dengan filter bulan ini
        $res = $this->get('/admin/dashboard?month=' . $now->month . '&year=' . $now->year);
        $res->assertStatus(200);
        $res->assertSee('Ringkasan Evaluasi Kinerja PST');
        $res->assertSee('Selesai Dilayani');
        $res->assertSee('Tamu Bulan Ini 1');
        $res->assertSee('Tamu Bulan Ini 2');
        $res->assertDontSee('Tamu Bulan Lalu');

        // Komparasi MoM: dari 1 menjadi 2 kunjungan (+100%)
        $res->assertSee('+100%');
    }

    public function test_admin_can_filter_and_search_reports(): void
    {
        $serviceA = Service::where('code_prefix', 'A')->first();
        $serviceB = Service::where('code_prefix', 'B')->first();

        $today = now()->toDateString();

        // Tamu 1
        $q1 = Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $serviceA->id,
            'status' => 'completed',
        ]);
        GuestBook::create([
            'queue_id' => $q1->id,
            'full_name' => 'Budi Santoso',
            'phone_number' => '0811111111',
            'institution_name' => 'Dinas Pendidikan Kota Tasikmalaya',
            'institution_type' => 'pemerintah',
            'data_needs_description' => 'Statistik Sekolah',
        ]);

        // Tamu 2
        $q2 = Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'B-001',
            'ticket_number' => 1,
            'service_id' => $serviceB->id,
            'status' => 'completed',
        ]);
        GuestBook::create([
            'queue_id' => $q2->id,
            'full_name' => 'Ani Wijaya',
            'phone_number' => '0822222222',
            'institution_name' => 'PT Sumber Makmur',
            'institution_type' => 'swasta',
            'data_needs_description' => 'Data Inflasi',
        ]);

        $this->actingAs($this->admin);

        // 1. Filter pencarian teks nama 'Budi'
        $searchRes = $this->get('/admin/reports?search=Budi');
        $searchRes->assertStatus(200);
        $searchRes->assertSee('Budi Santoso');
        $searchRes->assertDontSee('Ani Wijaya');

        // 2. Filter kategori instansi 'swasta'
        $categoryRes = $this->get('/admin/reports?institution_type=swasta');
        $categoryRes->assertStatus(200);
        $categoryRes->assertSee('Ani Wijaya');
        $categoryRes->assertDontSee('Budi Santoso');

        // 3. Filter layanan PST prefix A
        $serviceRes = $this->get('/admin/reports?service_id=' . $serviceA->id);
        $serviceRes->assertStatus(200);
        $serviceRes->assertSee('Budi Santoso');
        $serviceRes->assertDontSee('Ani Wijaya');
    }

    public function test_export_csv_contains_utf8_bom_and_proper_content(): void
    {
        $service = Service::first();
        $q = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $service->id,
            'status' => 'completed',
            'wait_duration_seconds' => 150,
            'serve_duration_seconds' => 450,
        ]);
        GuestBook::create([
            'queue_id' => $q->id,
            'full_name' => 'Dewi Sartika',
            'phone_number' => '0833333333',
            'institution_name' => 'Fakultas Ekonomi Unsil',
            'institution_type' => 'pendidikan',
            'data_needs_description' => 'Publikasi Tasikmalaya Dalam Angka',
        ]);

        $this->actingAs($this->admin);

        $res = $this->get('/admin/reports/export-csv');
        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Pastikan isi stream mengandung UTF-8 BOM dan nama tamu
        $content = $res->streamedContent();
        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
        $this->assertStringStartsWith($bom, $content);
        $this->assertStringContainsString('Dewi Sartika', $content);
        $this->assertStringContainsString('Fakultas Ekonomi Unsil', $content);
        $this->assertStringContainsString('A-001', $content);
    }

    public function test_export_pdf_generates_valid_pdf_document(): void
    {
        $service = Service::first();
        $q = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $service->id,
            'status' => 'completed',
            'wait_duration_seconds' => 120,
            'serve_duration_seconds' => 300,
        ]);
        GuestBook::create([
            'queue_id' => $q->id,
            'full_name' => 'Ahmad Dahlan',
            'phone_number' => '0844444444',
            'institution_name' => 'Bappeda Kota Tasikmalaya',
            'institution_type' => 'pemerintah',
            'data_needs_description' => 'Data Ketenagakerjaan',
        ]);

        $this->actingAs($this->admin);

        $res = $this->get('/admin/reports/export-pdf');
        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'application/pdf');

        $content = $res->getContent();
        // Validasi magic number PDF: '%PDF-'
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_admin_dashboard_and_reports_use_unified_workspace_readable_labels_and_no_cdn_or_emojis(): void
    {
        $service = Service::first();
        $q = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-099',
            'ticket_number' => 99,
            'service_id' => $service->id,
            'status' => 'completed',
        ]);
        GuestBook::create([
            'queue_id' => $q->id,
            'full_name' => 'Rina Kartika',
            'phone_number' => '0811223344',
            'institution_name' => 'Universitas Siliwangi',
            'institution_type' => 'pendidikan',
            'data_needs_description' => 'Data PDRB Kota Tasikmalaya',
        ]);

        $this->actingAs($this->admin);

        $dashboard = $this->get('/admin/dashboard')->assertStatus(200);
        $dashHtml = $dashboard->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $dashHtml);
        $this->assertStringNotContainsString('👥', $dashHtml);
        $this->assertStringNotContainsString('⏳', $dashHtml);
        $this->assertStringNotContainsString('⏱️', $dashHtml);
        $this->assertStringContainsString('aria-current="page"', $dashHtml);
        $this->assertStringContainsString(route('password.change'), $dashHtml);

        $reports = $this->get('/admin/reports')->assertStatus(200);
        $repHtml = $reports->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $repHtml);
        $this->assertStringNotContainsString('🔍', $repHtml);
        $this->assertStringNotContainsString('📥', $repHtml);
        $this->assertStringNotContainsString('📄', $repHtml);
        $this->assertStringContainsString('Pendidikan / Riset', $repHtml);
        $this->assertStringContainsString(route('password.change'), $repHtml);
    }
}
