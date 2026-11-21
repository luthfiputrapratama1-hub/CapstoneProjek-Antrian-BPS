<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\GuestBook;
use App\Models\Queue;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CounterController extends Controller
{
    public function index(Request $request): View
    {
        $counters = Counter::where('is_active', true)->orderBy('counter_number')->get();
        $selectedCounterId = $request->get('counter_id', $counters->first()?->id);
        $selectedCounter = Counter::with('services')->find($selectedCounterId);

        $today = now()->toDateString();
        $activeQueue = Queue::with('service', 'guestBook')
            ->whereDate('queue_date', $today)
            ->where('counter_id', $selectedCounterId)
            ->whereIn('status', ['calling', 'serving'])
            ->first();

        $serviceIds = $selectedCounter?->services->pluck('id')->all() ?? [];

        $waitingCount = Queue::whereDate('queue_date', $today)
            ->where('status', 'waiting')
            ->whereIn('service_id', $serviceIds)
            ->count();

        // Daftar antrean yang dilewati (skipped) hari ini yang belum dipanggil ulang
        $skippedQueues = Queue::with('service')
            ->whereDate('queue_date', $today)
            ->where('status', 'skipped')
            ->whereIn('service_id', $serviceIds)
            ->whereNull('recalled_skipped_at')
            ->orderBy('id')
            ->get();

        $canSkip = false;
        if ($activeQueue) {
            $canSkip = Auth::user()?->role === 'admin' || $activeQueue->recall_count >= 3;
        }

        return view('counter.index', compact(
            'counters', 'selectedCounter', 'activeQueue', 'waitingCount', 'skippedQueues', 'canSkip'
        ));
    }

    public function callNext(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'counter_id' => ['required', 'exists:counters,id'],
        ], [
            'counter_id.required' => 'Identitas loket wajib disertakan.',
            'counter_id.exists' => 'Loket yang dipilih tidak ditemukan dalam sistem.',
        ]);

        return DB::transaction(function () use ($validated) {
            $counter = Counter::with('services')->findOrFail($validated['counter_id']);
            $serviceIds = $counter->services->pluck('id')->all();
            $today = now()->toDateString();

            // Aturan: Satu loket tidak boleh memanggil nomor baru selama masih ada tiket calling / serving
            $existingActive = Queue::whereDate('queue_date', $today)
                ->where('counter_id', $counter->id)
                ->whereIn('status', ['calling', 'serving'])
                ->lockForUpdate()
                ->first();

            if ($existingActive) {
                return response()->json([
                    'status' => 'busy',
                    'message' => "Loket {$counter->name} masih menangani tiket {$existingActive->ticket_code}. Selesaikan atau lewati tiket tersebut terlebih dahulu.",
                ], 422);
            }

            // Tarik antrean waiting terlama yang sesuai dengan penugasan layanan loket (FIFO)
            $nextQueue = Queue::whereDate('queue_date', $today)
                ->where('status', 'waiting')
                ->whereIn('service_id', $serviceIds)
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (!$nextQueue) {
                return response()->json([
                    'status' => 'empty',
                    'message' => 'Tidak ada antrean menunggu untuk kategori layanan yang ditugaskan ke loket ini.',
                ], 404);
            }

            $calledAt = now();
            // Durasi tunggu: waktu dipanggil dikurangi waktu tiket terbit (tidak boleh negatif)
            $waitDuration = max(0, (int) $calledAt->diffInSeconds($nextQueue->created_at, false));

            $nextQueue->update([
                'status' => 'calling',
                'counter_id' => $counter->id,
                'user_id' => Auth::id(),
                'called_at' => $calledAt,
                'recall_count' => 0,
                'wait_duration_seconds' => $waitDuration,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Antrean {$nextQueue->ticket_code} berhasil dipanggil.",
                'data' => $nextQueue->load('service', 'counter'),
            ]);
        });
    }

    public function recall(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'queue_id' => ['required', 'exists:queues,id'],
        ]);

        return DB::transaction(function () use ($validated) {
            $queue = Queue::with('service', 'counter')->lockForUpdate()->findOrFail($validated['queue_id']);

            // Validasi status
            if ($queue->status !== 'calling') {
                return response()->json([
                    'status' => 'invalid_status',
                    'message' => 'Hanya antrean berstatus sedang dipanggil (calling) yang dapat dipanggil ulang.',
                ], 422);
            }

            // Validasi batasan recall maksimal 3 kali
            if ($queue->recall_count >= 3) {
                return response()->json([
                    'status' => 'max_recall_reached',
                    'message' => 'Batas maksimal panggil ulang (3 kali) telah tercapai. Anda sekarang dapat melewati antrean ini atau memulai pelayanan.',
                ], 422);
            }

            $queue->increment('recall_count');
            $queue->touch(); // Perbarui updated_at agar tertangkap oleh aliran SSE

            return response()->json([
                'status' => 'success',
                'message' => "Panggilan ulang ke-{$queue->recall_count} untuk {$queue->ticket_code} berhasil dikirim.",
                'data' => $queue,
            ]);
        });
    }

    public function startServing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'queue_id' => ['required', 'exists:queues,id'],
        ]);

        return DB::transaction(function () use ($validated) {
            $queue = Queue::lockForUpdate()->findOrFail($validated['queue_id']);

            // Validasi transisi status: hanya boleh dari calling -> serving
            if ($queue->status !== 'calling') {
                return response()->json([
                    'status' => 'invalid_status',
                    'message' => 'Status antrean tidak valid untuk memulai pelayanan. Antrean harus berstatus dipanggil (calling).',
                ], 422);
            }

            $servedAt = now();

            $queue->update([
                'status' => 'serving',
                'served_at' => $servedAt,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Pelayanan untuk {$queue->ticket_code} telah dimulai.",
                'data' => $queue,
            ]);
        });
    }

    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'queue_id' => ['required', 'exists:queues,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:255'],
            'institution_name' => ['required', 'string', 'max:255'],
            'institution_type' => ['required', 'in:pendidikan,pemerintah,swasta,lsm,umum'],
            'data_needs_description' => ['required', 'string'],
            'pdp_consent' => ['sometimes', 'accepted'],
        ], [
            'full_name.required' => 'Nama lengkap pengunjung wajib diisi.',
            'phone_number.required' => 'Nomor WhatsApp / telepon pengunjung wajib diisi.',
            'institution_name.required' => 'Nama instansi asal pengunjung wajib diisi.',
            'institution_type.required' => 'Kategori instansi pemohon wajib dipilih.',
            'data_needs_description.required' => 'Rincian kebutuhan data atau maksud kunjungan wajib diisi.',
            'pdp_consent.accepted' => 'Pernyataan persetujuan penggunaan data pribadi (UU PDP) wajib disetujui.',
        ]);

        return DB::transaction(function () use ($validated) {
            $queue = Queue::lockForUpdate()->findOrFail($validated['queue_id']);

            // Validasi transisi status: hanya boleh dari serving -> completed
            if ($queue->status !== 'serving') {
                return response()->json([
                    'status' => 'invalid_status',
                    'message' => 'Antrean tidak dapat diselesaikan karena belum dalam status dilayani (serving). Silakan mulai pelayanan terlebih dahulu.',
                ], 422);
            }

            GuestBook::updateOrCreate(
                ['queue_id' => $queue->id],
                [
                    'full_name' => $validated['full_name'],
                    'phone_number' => $validated['phone_number'],
                    'email' => $validated['email'] ?? null,
                    'institution_name' => $validated['institution_name'],
                    'institution_type' => $validated['institution_type'],
                    'data_needs_description' => $validated['data_needs_description'],
                ]
            );

            $completedAt = now();
            // Durasi layanan: completed_at - served_at (tidak boleh negatif)
            $serveDuration = $queue->served_at
                ? max(0, (int) $completedAt->diffInSeconds($queue->served_at, false))
                : 0;

            $queue->update([
                'status' => 'completed',
                'completed_at' => $completedAt,
                'serve_duration_seconds' => $serveDuration,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Antrean {$queue->ticket_code} berhasil diselesaikan dan data buku tamu telah dicatat.",
                'data' => $queue,
            ]);
        });
    }

    public function skip(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'queue_id' => ['required', 'exists:queues,id'],
        ]);

        return DB::transaction(function () use ($validated) {
            $queue = Queue::lockForUpdate()->findOrFail($validated['queue_id']);

            // Validasi status: hanya boleh dari calling -> skipped
            if ($queue->status !== 'calling') {
                return response()->json([
                    'status' => 'invalid_status',
                    'message' => 'Hanya antrean berstatus sedang dipanggil (calling) yang dapat dilewati.',
                ], 422);
            }

            // Aturan bisnis: Lewati (skip) baru aktif setelah recall ke-3, kecuali jika pengguna adalah admin
            $user = Auth::user();
            if ($user?->role !== 'admin' && $queue->recall_count < 3) {
                return response()->json([
                    'status' => 'skip_not_allowed',
                    'message' => "Antrean baru dapat dilewati setelah dipanggil ulang sebanyak 3 kali (saat ini baru {$queue->recall_count} kali).",
                ], 422);
            }

            $queue->update([
                'status' => 'skipped',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Antrean {$queue->ticket_code} telah dilewati.",
                'data' => $queue,
            ]);
        });
    }

    public function recallSkipped(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'queue_id' => ['required', 'exists:queues,id'],
        ]);

        return DB::transaction(function () use ($validated) {
            $today = now()->toDateString();
            $queue = Queue::lockForUpdate()->findOrFail($validated['queue_id']);

            // Validasi bahwa tiket berstatus skipped dan dari hari ini
            if ($queue->status !== 'skipped' || $queue->queue_date->toDateString() !== $today) {
                return response()->json([
                    'status' => 'invalid_status',
                    'message' => 'Hanya antrean berstatus dilewati (skipped) pada hari ini yang dapat dipanggil ulang.',
                ], 422);
            }

            // Validasi aturan: hanya boleh dipanggil ulang sekali dalam hari yang sama
            if ($queue->recalled_skipped_at !== null) {
                return response()->json([
                    'status' => 'already_recalled',
                    'message' => "Antrean {$queue->ticket_code} sudah pernah dipanggil ulang sebelumnya dan tidak dapat dipanggil kembali.",
                ], 422);
            }

            // Tempatkan kembali di urutan paling depan layanannya:
            // Set created_at ke waktu sebelum tiket waiting terawal pada hari ini
            $earliestWaiting = Queue::whereDate('queue_date', $today)
                ->where('service_id', $queue->service_id)
                ->where('status', 'waiting')
                ->orderBy('created_at')
                ->first();

            $newCreatedAt = $earliestWaiting
                ? $earliestWaiting->created_at->subSecond()
                : now()->startOfDay();

            $queue->forceFill([
                'status' => 'waiting',
                'counter_id' => null,
                'user_id' => null,
                'called_at' => null,
                'served_at' => null,
                'completed_at' => null,
                'recall_count' => 0,
                'wait_duration_seconds' => null,
                'serve_duration_seconds' => null,
                'recalled_skipped_at' => now(),
                'created_at' => $newCreatedAt,
            ])->save();

            return response()->json([
                'status' => 'success',
                'message' => "Antrean {$queue->ticket_code} berhasil dikembalikan ke barisan antrean terdepan layanan {$queue->service->name}.",
                'data' => $queue->load('service'),
            ]);
        });
    }
}
