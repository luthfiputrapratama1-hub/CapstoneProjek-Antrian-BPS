<?php

namespace Tests\Feature;

use App\Models\Queue;
use App\Models\Service;
use App\Services\QueueNumberService;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueNumberingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
    }

    public function test_consecutive_ticket_numbering_per_service(): void
    {
        $serviceA = Service::where('code_prefix', 'A')->first();
        $serviceB = Service::where('code_prefix', 'B')->first();

        $queueService = app(QueueNumberService::class);

        $ticketA1 = $queueService->generateTicket($serviceA->id);
        $this->assertEquals('A-001', $ticketA1['ticket_code']);
        $this->assertEquals(0, $ticketA1['waiting_ahead']);

        $ticketA2 = $queueService->generateTicket($serviceA->id);
        $this->assertEquals('A-002', $ticketA2['ticket_code']);
        $this->assertEquals(1, $ticketA2['waiting_ahead']);

        $ticketB1 = $queueService->generateTicket($serviceB->id);
        $this->assertEquals('B-001', $ticketB1['ticket_code']);
        $this->assertEquals(0, $ticketB1['waiting_ahead']);
    }

    public function test_ticket_numbering_resets_on_new_day(): void
    {
        $serviceA = Service::where('code_prefix', 'A')->first();

        // Buat antrean untuk tanggal kemarin
        Queue::create([
            'queue_date' => now()->subDay()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $serviceA->id,
            'status' => 'completed',
        ]);
        Queue::create([
            'queue_date' => now()->subDay()->toDateString(),
            'ticket_code' => 'A-002',
            'ticket_number' => 2,
            'service_id' => $serviceA->id,
            'status' => 'completed',
        ]);

        // Buat antrean untuk hari ini
        $queueService = app(QueueNumberService::class);
        $todayTicket = $queueService->generateTicket($serviceA->id);

        $this->assertEquals('A-001', $todayTicket['ticket_code']);
        $this->assertEquals(0, $todayTicket['waiting_ahead']);
    }
}
