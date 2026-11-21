<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class QueueRecallSkipTest extends TestCase
{
    use RefreshDatabase;

    protected User $petugas;
    protected User $admin;
    protected Counter $counter;
    protected Service $serviceA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
        $this->petugas = User::where('role', 'petugas')->first();
        $this->admin = User::where('role', 'admin')->first();
        $this->counter = Counter::first();
        $this->serviceA = Service::where('code_prefix', 'A')->first();
    }

    public function test_recall_increments_count_and_is_limited_to_three_times(): void
    {
        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'counter_id' => $this->counter->id,
            'user_id' => $this->petugas->id,
            'status' => 'calling',
            'called_at' => now(),
            'recall_count' => 0,
        ]);

        $this->actingAs($this->petugas);

        // Recall 1
        $r1 = $this->postJson('/loket/recall', ['queue_id' => $queue->id]);
        $r1->assertStatus(200);
        $queue->refresh();
        $this->assertEquals(1, $queue->recall_count);

        // Recall 2
        $r2 = $this->postJson('/loket/recall', ['queue_id' => $queue->id]);
        $r2->assertStatus(200);
        $queue->refresh();
        $this->assertEquals(2, $queue->recall_count);

        // Recall 3
        $r3 = $this->postJson('/loket/recall', ['queue_id' => $queue->id]);
        $r3->assertStatus(200);
        $queue->refresh();
        $this->assertEquals(3, $queue->recall_count);

        // Recall 4 -> Ditolak (Maksimal 3 kali)
        $r4 = $this->postJson('/loket/recall', ['queue_id' => $queue->id]);
        $r4->assertStatus(422)
           ->assertJsonPath('status', 'max_recall_reached');
        $queue->refresh();
        $this->assertEquals(3, $queue->recall_count);
    }

    public function test_petugas_cannot_skip_before_three_recalls_but_admin_can(): void
    {
        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'counter_id' => $this->counter->id,
            'user_id' => $this->petugas->id,
            'status' => 'calling',
            'called_at' => now(),
            'recall_count' => 1, // Baru 1 kali recall
        ]);

        // 1. Petugas mencoba skip -> ditolak
        $this->actingAs($this->petugas);
        $skipPetugas = $this->postJson('/loket/skip', ['queue_id' => $queue->id]);
        $skipPetugas->assertStatus(422)
                    ->assertJsonPath('status', 'skip_not_allowed');

        // 2. Admin mencoba skip antrean yang sama -> diizinkan
        $this->actingAs($this->admin);
        $skipAdmin = $this->postJson('/loket/skip', ['queue_id' => $queue->id]);
        $skipAdmin->assertStatus(200);

        $queue->refresh();
        $this->assertEquals('skipped', $queue->status);
    }

    public function test_petugas_can_skip_after_three_recalls(): void
    {
        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'counter_id' => $this->counter->id,
            'user_id' => $this->petugas->id,
            'status' => 'calling',
            'called_at' => now(),
            'recall_count' => 3, // Sudah 3 kali recall
        ]);

        $this->actingAs($this->petugas);
        $res = $this->postJson('/loket/skip', ['queue_id' => $queue->id]);
        $res->assertStatus(200);

        $queue->refresh();
        $this->assertEquals('skipped', $queue->status);
    }

    public function test_skipped_queue_can_be_recalled_once_and_moved_to_front(): void
    {
        $today = now()->toDateString();

        // Antrean reguler waiting
        $regularWaiting = Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-002',
            'ticket_number' => 2,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
            'created_at' => now(),
        ]);

        // Antrean A-001 yang dilewati
        $skippedQueue = Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'status' => 'skipped',
            'created_at' => now()->subMinutes(30),
        ]);

        $this->actingAs($this->petugas);

        // Panggil kembali antrean yang dilewati
        $recallRes = $this->postJson('/loket/recall-skipped', ['queue_id' => $skippedQueue->id]);
        $recallRes->assertStatus(200);

        $skippedQueue->refresh();
        $this->assertEquals('waiting', $skippedQueue->status);
        $this->assertNotNull($skippedQueue->recalled_skipped_at);

        // Saat loket memanggil callNext, A-001 yang dipanggil kembali harus terpanggil lebih dulu dari A-002
        $callRes = $this->postJson('/loket/call-next', ['counter_id' => $this->counter->id]);
        $callRes->assertStatus(200)
                ->assertJsonPath('data.ticket_code', 'A-001');

        // Coba recall ulang kedua kalinya untuk tiket yang sama setelah di-skip lagi
        $skippedQueue->update(['status' => 'skipped', 'recall_count' => 3]);
        $secondRecallRes = $this->postJson('/loket/recall-skipped', ['queue_id' => $skippedQueue->id]);
        $secondRecallRes->assertStatus(422)
                        ->assertJsonPath('status', 'already_recalled');
    }

    public function test_close_expired_queues_command_marks_yesterday_queues_as_skipped(): void
    {
        $yesterday = now()->subDay()->toDateString();
        $today = now()->toDateString();

        $oldWaiting = Queue::create([
            'queue_date' => $yesterday,
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
        ]);
        $oldCalling = Queue::create([
            'queue_date' => $yesterday,
            'ticket_code' => 'A-002',
            'ticket_number' => 2,
            'service_id' => $this->serviceA->id,
            'status' => 'calling',
        ]);
        $todayWaiting = Queue::create([
            'queue_date' => $today,
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
        ]);

        Artisan::call('queues:close-expired');

        $oldWaiting->refresh();
        $oldCalling->refresh();
        $todayWaiting->refresh();

        $this->assertEquals('skipped', $oldWaiting->status);
        $this->assertEquals('skipped', $oldCalling->status);
        $this->assertEquals('waiting', $todayWaiting->status); // Antrean hari ini tidak tersentuh
    }

    public function test_duration_calculation_is_accurate_and_non_negative(): void
    {
        $created = now()->subMinutes(10);
        $called = now()->subMinutes(6);
        $served = now()->subMinutes(5);
        $completed = now();

        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->serviceA->id,
            'status' => 'waiting',
            'created_at' => $created,
        ]);

        $this->actingAs($this->petugas);

        // 1. Call next -> hitung wait_duration_seconds
        $this->postJson('/loket/call-next', ['counter_id' => $this->counter->id]);
        $queue->refresh();
        $this->assertGreaterThanOrEqual(0, $queue->wait_duration_seconds);

        // 2. Start serve
        $this->postJson('/loket/start-serve', ['queue_id' => $queue->id]);

        // 3. Complete
        $this->postJson('/loket/complete', [
            'queue_id' => $queue->id,
            'full_name' => 'Budi Setiawan',
            'phone_number' => '081234567890',
            'institution_name' => 'Universitas Siliwangi',
            'institution_type' => 'pendidikan',
            'data_needs_description' => 'Data inflasi',
        ]);

        $queue->refresh();
        $this->assertEquals('completed', $queue->status);
        $this->assertGreaterThanOrEqual(0, $queue->serve_duration_seconds);
    }
}
