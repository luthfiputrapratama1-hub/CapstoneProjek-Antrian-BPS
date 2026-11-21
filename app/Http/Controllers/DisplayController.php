<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisplayController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $counters = Counter::where('is_active', true)->orderBy('counter_number')->get();
        $recentQueues = Queue::with('counter', 'service')
            ->whereDate('queue_date', $today)
            ->whereIn('status', ['calling', 'serving', 'completed'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        $services = Service::where('is_active', true)
            ->withCount(['queues' => function ($q) use ($today) {
                $q->whereDate('queue_date', $today)->where('status', 'waiting');
            }])
            ->orderBy('order_priority')
            ->get();

        return view('display.index', compact('counters', 'recentQueues', 'services'));
    }

    public function pollUpdates(Request $request): JsonResponse
    {
        $today = now()->toDateString();
        $activeQueues = Queue::with('counter', 'service')
            ->whereDate('queue_date', $today)
            ->whereIn('status', ['calling', 'serving'])
            ->get();

        $recentQueues = Queue::with('counter', 'service')
            ->whereDate('queue_date', $today)
            ->whereIn('status', ['calling', 'serving', 'completed'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        $services = Service::where('is_active', true)
            ->withCount(['queues' => function ($q) use ($today) {
                $q->whereDate('queue_date', $today)->where('status', 'waiting');
            }])
            ->orderBy('order_priority')
            ->get();

        $latestCalling = Queue::with('counter', 'service')
            ->whereDate('queue_date', $today)
            ->where('status', 'calling')
            ->orderByDesc('updated_at')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'active_queues' => $activeQueues,
                'recent_queues' => $recentQueues,
                'services' => $services,
                'latest_calling' => $latestCalling ? [
                    'ticket_code' => $latestCalling->ticket_code,
                    'code_prefix' => $latestCalling->service->code_prefix,
                    'ticket_number' => $latestCalling->ticket_number,
                    'counter_number' => $latestCalling->counter?->counter_number ?? 1,
                    'counter_name' => $latestCalling->counter?->name ?? 'Loket',
                    'service_name' => $latestCalling->service->name,
                    'updated_at' => $latestCalling->updated_at->toIso8601String(),
                ] : null,
            ]
        ]);
    }

    public function streamUpdates(Request $request): StreamedResponse
    {
        return response()->stream(function () {
            if (!app()->runningUnitTests() && ob_get_level()) {
                ob_end_clean();
            }

            $today = now()->toDateString();
            $lastChecked = now()->subSeconds(3)->toDateTimeString();

            // Kirim status awal (snapshot) beserta status layanan
            $activeQueues = Queue::with('counter', 'service')
                ->whereDate('queue_date', $today)
                ->whereIn('status', ['calling', 'serving'])
                ->get();

            $services = Service::where('is_active', true)
                ->withCount(['queues' => function ($q) use ($today) {
                    $q->whereDate('queue_date', $today)->where('status', 'waiting');
                }])
                ->orderBy('order_priority')
                ->get();

            echo "event: initial-state\n";
            echo 'data: ' . json_encode([
                'active_queues' => $activeQueues,
                'services' => $services,
            ]) . "\n\n";
            flush();

            // Jika dalam unit testing, cukup kirim snapshot dan keluar agar tidak blocking
            if (app()->runningUnitTests()) {
                return;
            }

            // Loop SSE streaming untuk peramban nyata (12 detik per siklus koneksi agar ramah PHP-CLI worker)
            $startTime = time();
            while (time() - $startTime < 12) {
                if (connection_aborted()) {
                    break;
                }

                $newCalls = Queue::with('counter', 'service')
                    ->whereDate('queue_date', $today)
                    ->where('status', 'calling')
                    ->where('updated_at', '>=', $lastChecked)
                    ->get();

                foreach ($newCalls as $call) {
                    echo "event: queue-called\n";
                    echo 'data: ' . json_encode([
                        'ticket_code' => $call->ticket_code,
                        'code_prefix' => $call->service->code_prefix,
                        'ticket_number' => $call->ticket_number,
                        'counter_number' => $call->counter?->counter_number ?? 1,
                        'counter_name' => $call->counter?->name ?? 'Loket',
                        'service_name' => $call->service->name,
                        'updated_at' => $call->updated_at->toIso8601String(),
                    ]) . "\n\n";
                    flush();
                }

                $lastChecked = now()->toDateTimeString();
                echo ": heartbeat\n\n";
                flush();
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=UTF-8',
            'Cache-Control' => 'no-cache, private',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
