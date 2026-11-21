<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SseStreamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
    }

    public function test_sse_endpoint_returns_correct_event_stream_headers(): void
    {
        $response = $this->get('/display/stream');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
        $response->assertHeader('Cache-Control', 'no-cache, private');
    }

    public function test_sse_endpoint_sends_initial_state_event(): void
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

        $response = $this->get('/display/stream');
        $content = $response->streamedContent();

        $this->assertStringContainsString('event: initial-state', $content);
        $this->assertStringContainsString('A-001', $content);
    }
}
