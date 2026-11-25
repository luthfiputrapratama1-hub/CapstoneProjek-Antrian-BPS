<?php

namespace Tests\Feature;

use App\Models\Service;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
    }

    public function test_kiosk_page_displays_active_services(): void
    {
        $response = $this->get('/kiosk');
        $response->assertStatus(200);
        $response->assertSee('Konsultasi Statistik');
        $response->assertSee('Perpustakaan Statistik');
    }

    public function test_can_generate_ticket_with_consecutive_numbering_per_service(): void
    {
        $serviceA = Service::where('code_prefix', 'A')->first();
        $serviceB = Service::where('code_prefix', 'B')->first();

        $resA1 = $this->postJson('/kiosk/ticket', ['service_id' => $serviceA->id]);
        $resA1->assertStatus(200)
              ->assertJsonPath('data.ticket_code', 'A-001')
              ->assertJsonPath('data.waiting_ahead', 0);

        $resA2 = $this->postJson('/kiosk/ticket', ['service_id' => $serviceA->id]);
        $resA2->assertStatus(200)
              ->assertJsonPath('data.ticket_code', 'A-002')
              ->assertJsonPath('data.waiting_ahead', 1);

        $resB1 = $this->postJson('/kiosk/ticket', ['service_id' => $serviceB->id]);
        $resB1->assertStatus(200)
              ->assertJsonPath('data.ticket_code', 'B-001')
              ->assertJsonPath('data.waiting_ahead', 0);
    }

    public function test_kiosk_page_fixes_print_visibility_uses_native_dialogs_and_has_no_cdn_or_emojis(): void
    {
        $response = $this->get('/kiosk');
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('⚠️', $html);
        $this->assertStringContainsString('<dialog id="ticket-modal"', $html);
        $this->assertStringContainsString('<dialog id="error-modal"', $html);
        $this->assertStringContainsString('id="ticket-print-area" class="print-only"', $html);
        $this->assertStringNotContainsString('id="ticket-print-area" class="hidden"', $html);
    }
}
