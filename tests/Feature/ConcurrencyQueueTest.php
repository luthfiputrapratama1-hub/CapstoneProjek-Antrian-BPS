<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrencyQueueTest extends TestCase
{
    use RefreshDatabase;

    protected User $petugas;
    protected Counter $counter1;
    protected Counter $counter2;
    protected Service $serviceA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
        $this->petugas = User::where('role', 'petugas')->first();
        $this->counter1 = Counter::where('counter_number', 1)->first();
        $this->counter2 = Counter::where('counter_number', 2)->first();
        $this->serviceA = Service::where('code_prefix', 'A')->first();
    }

    public function test_counter_cannot_call_next_if_still_has_calling_ticket(): void
    {
        $today = now()->toDateString();

        // Buat antrean 1 dan 2
        $q1 = Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
        ]);
        $q2 = Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-002',
            'ticket_number' => 2,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
        ]);

        $this->actingAs($this->petugas);

        // Panggilan pertama oleh Loket 1 -> sukses
        $res1 = $this->postJson('/loket/call-next', ['counter_id' => $this->counter1->id]);
        $res1->assertStatus(200)
             ->assertJsonPath('data.ticket_code', 'A-001')
             ->assertJsonPath('data.status', 'calling');

        // Panggilan kedua oleh Loket 1 yang sama tanpa menyelesaikan A-001 -> ditolak
        $res2 = $this->postJson('/loket/call-next', ['counter_id' => $this->counter1->id]);
        $res2->assertStatus(422)
             ->assertJsonPath('status', 'busy');

        // Pastikan tiket kedua masih berstatus waiting
        $q2->refresh();
        $this->assertEquals('waiting', $q2->status);
    }

    public function test_counter_cannot_call_next_if_still_has_serving_ticket(): void
    {
        $today = now()->toDateString();

        $q1 = Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
        ]);
        $q2 = Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-002',
            'ticket_number' => 2,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
        ]);

        $this->actingAs($this->petugas);

        $this->postJson('/loket/call-next', ['counter_id' => $this->counter1->id]);
        $this->postJson('/loket/start-serve', ['queue_id' => $q1->id]);

        // Coba panggil lagi saat masih status serving
        $res = $this->postJson('/loket/call-next', ['counter_id' => $this->counter1->id]);
        $res->assertStatus(422)
            ->assertJsonPath('status', 'busy');
    }

    public function test_two_different_counters_receive_different_tickets(): void
    {
        $today = now()->toDateString();

        Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
        ]);
        Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-002',
            'ticket_number' => 2,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
        ]);

        $this->actingAs($this->petugas);

        $call1 = $this->postJson('/loket/call-next', ['counter_id' => $this->counter1->id]);
        $call1->assertStatus(200)->assertJsonPath('data.ticket_code', 'A-001');

        $call2 = $this->postJson('/loket/call-next', ['counter_id' => $this->counter2->id]);
        $call2->assertStatus(200)->assertJsonPath('data.ticket_code', 'A-002');
    }
}
