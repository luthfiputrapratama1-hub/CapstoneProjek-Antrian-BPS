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
}
