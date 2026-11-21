<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\GuestBook;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_seed_and_relate_all_models(): void
    {
        $this->seed(\Database\Seeders\PstBpsSeeder::class);

        $this->assertDatabaseCount('services', 4);
        $this->assertDatabaseCount('counters', 2);
        $this->assertDatabaseHas('users', ['username' => 'admin', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['username' => 'petugas1', 'role' => 'petugas']);

        $service = Service::where('code_prefix', 'A')->first();
        $counter = Counter::where('counter_number', 1)->first();
        $user = User::where('role', 'petugas')->first();

        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $service->id,
            'counter_id' => $counter->id,
            'user_id' => $user->id,
            'status' => 'serving',
            'called_at' => now(),
            'served_at' => now(),
        ]);

        $guest = GuestBook::create([
            'queue_id' => $queue->id,
            'full_name' => 'Budi Santoso',
            'phone_number' => '081234567890',
            'email' => 'budi@example.com',
            'institution_name' => 'Universitas Siliwangi',
            'institution_type' => 'pendidikan',
            'data_needs_description' => 'Konsultasi data inflasi Tasikmalaya',
        ]);

        $this->assertEquals('A-001', $guest->queue->ticket_code);
        $this->assertEquals('Budi Santoso', $queue->guestBook->full_name);
        $this->assertTrue($counter->services->contains($service));
    }
}
