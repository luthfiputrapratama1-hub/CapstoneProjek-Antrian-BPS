<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounterOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $petugas;
    protected Counter $counter;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
        $this->petugas = User::where('role', 'petugas')->first();
        $this->counter = Counter::first();
        $this->service = Service::first();
    }

    public function test_guest_cannot_access_loket_page(): void
    {
        $response = $this->get('/loket');
        $response->assertRedirect('/login');
    }

    public function test_petugas_can_call_next_queue_and_complete_with_guest_book(): void
    {
        // 1. Buat antrean menunggu
        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->service->id,
            'status' => 'waiting',
        ]);

        // 2. Petugas login & panggil nomor berikutnya
        $this->actingAs($this->petugas);
        $callRes = $this->postJson('/loket/call-next', [
            'counter_id' => $this->counter->id,
        ]);
        $callRes->assertStatus(200)
                ->assertJsonPath('data.ticket_code', 'A-001')
                ->assertJsonPath('data.status', 'calling');

        // 3. Mulai melayani
        $serveRes = $this->postJson('/loket/start-serve', ['queue_id' => $queue->id]);
        $serveRes->assertStatus(200)
                 ->assertJsonPath('data.status', 'serving');

        // 4. Selesaikan dengan buku tamu
        $completeRes = $this->postJson('/loket/complete', [
            'queue_id' => $queue->id,
            'full_name' => 'Dr. Hendra Wijaya',
            'phone_number' => '081298765432',
            'email' => 'hendra@univ.ac.id',
            'institution_name' => 'Fakultas Ekonomi Unsil',
            'institution_type' => 'pendidikan',
            'data_needs_description' => 'Permintaan data statistik kemiskinan dan PDRB Kota Tasikmalaya',
        ]);

        $completeRes->assertStatus(200)
                    ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('guest_books', [
            'queue_id' => $queue->id,
            'full_name' => 'Dr. Hendra Wijaya',
        ]);

        $queue->refresh();
        $this->assertEquals('completed', $queue->status);
        $this->assertNotNull($queue->wait_duration_seconds);
        $this->assertNotNull($queue->serve_duration_seconds);
    }
}
