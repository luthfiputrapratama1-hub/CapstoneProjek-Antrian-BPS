<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestBookValidationTest extends TestCase
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

    public function test_cannot_complete_queue_without_required_guest_book_fields(): void
    {
        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->service->id,
            'counter_id' => $this->counter->id,
            'user_id' => $this->petugas->id,
            'status' => 'serving',
            'called_at' => now()->subMinutes(5),
            'served_at' => now()->subMinutes(3),
        ]);

        $this->actingAs($this->petugas);

        // Kirim tanpa field wajib
        $res = $this->postJson('/loket/complete', [
            'queue_id' => $queue->id,
            'full_name' => '',
            'phone_number' => '',
            'institution_name' => '',
            'institution_type' => 'invalid_type',
            'data_needs_description' => '',
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['full_name', 'phone_number', 'institution_name', 'institution_type', 'data_needs_description']);

        $queue->refresh();
        $this->assertEquals('serving', $queue->status);
    }

    public function test_cannot_complete_queue_that_is_not_in_serving_status(): void
    {
        // Antrean masih status calling
        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->service->id,
            'counter_id' => $this->counter->id,
            'user_id' => $this->petugas->id,
            'status' => 'calling',
            'called_at' => now(),
        ]);

        $this->actingAs($this->petugas);

        $res = $this->postJson('/loket/complete', [
            'queue_id' => $queue->id,
            'full_name' => 'Asep Saepuloh',
            'phone_number' => '081234567890',
            'institution_name' => 'Bappeda',
            'institution_type' => 'pemerintah',
            'data_needs_description' => 'Data kemiskinan',
        ]);

        $res->assertStatus(422)
            ->assertJsonPath('status', 'invalid_status');

        $queue->refresh();
        $this->assertEquals('calling', $queue->status);
    }
}
