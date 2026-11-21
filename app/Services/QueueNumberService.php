<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class QueueNumberService
{
    public function generateTicket(int $serviceId): array
    {
        return DB::transaction(function () use ($serviceId) {
            $service = Service::where('is_active', true)->findOrFail($serviceId);
            $today = now()->toDateString();

            // Kunci baris transaksi tanggal & service untuk atomisitas
            $lastTicketNumber = Queue::whereDate('queue_date', $today)
                ->where('service_id', $service->id)
                ->lockForUpdate()
                ->max('ticket_number') ?? 0;

            $nextNumber = $lastTicketNumber + 1;
            $ticketCode = sprintf('%s-%03d', $service->code_prefix, $nextNumber);

            $queue = Queue::create([
                'queue_date' => $today,
                'ticket_code' => $ticketCode,
                'ticket_number' => $nextNumber,
                'service_id' => $service->id,
                'status' => 'waiting',
            ]);

            // Hitung antrean di depannya yang masih berstatus waiting
            $waitingAhead = Queue::whereDate('queue_date', $today)
                ->where('service_id', $service->id)
                ->where('status', 'waiting')
                ->where('id', '<', $queue->id)
                ->count();

            return [
                'queue_id' => $queue->id,
                'ticket_code' => $ticketCode,
                'service_name' => $service->name,
                'created_at' => $queue->created_at->format('d/m/Y H:i') . ' WIB',
                'waiting_ahead' => $waitingAhead,
                'mock_print' => (bool) config('app.kiosk_mock_print', true),
            ];
        });
    }
}
