<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\QueueNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KioskController extends Controller
{
    public function index(): View
    {
        $services = Service::where('is_active', true)->orderBy('order_priority')->get();
        return view('kiosk.index', compact('services'));
    }

    public function storeTicket(Request $request, QueueNumberService $service): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
        ]);

        $ticketData = $service->generateTicket((int) $validated['service_id']);

        return response()->json([
            'status' => 'success',
            'message' => 'Tiket berhasil dicetak',
            'data' => $ticketData,
        ]);
    }
}
