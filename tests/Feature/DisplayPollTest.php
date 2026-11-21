<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayPollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
    }

    public function test_poll_updates_endpoint_returns_json_structure(): void
    {
        $service = Service::first();
        $counter = Counter::first();

        Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $service->id,
            'counter_id' => $counter->id,
            'status' => 'calling',
            'called_at' => now(),
        ]);

        $response = $this->getJson('/display/poll-updates');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'active_queues',
                    'recent_queues',
                    'services',
                ]
            ]);

        $this->assertCount(1, $response->json('data.active_queues'));
        $this->assertEquals('A-001', $response->json('data.active_queues.0.ticket_code'));
    }
}
