<?php

namespace App\Http\Controllers;

use App\Models\GuestBook;
use App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function buildQuery(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $serviceId = $request->get('service_id');
        $institutionType = $request->get('institution_type');
        $search = $request->get('search');

        $query = GuestBook::with(['queue.service', 'queue.counter', 'queue.user'])
            ->whereHas('queue', function ($q) use ($startDate, $endDate, $serviceId) {
                $q->whereDate('queue_date', '>=', $startDate)
                  ->whereDate('queue_date', '<=', $endDate)
                  ->where('status', 'completed');
                if ($serviceId) {
                    $q->where('service_id', $serviceId);
                }
            });

        if ($institutionType) {
            $query->where('institution_type', $institutionType);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('institution_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhereHas('queue', function ($sub) use ($search) {
                      $sub->where('ticket_code', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    public function index(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $serviceId = $request->get('service_id');
        $institutionType = $request->get('institution_type');
        $search = $request->get('search');

        $services = Service::where('is_active', true)->orderBy('order_priority')->get();

        $visitors = $this->buildQuery($request)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', compact(
            'visitors', 'services', 'startDate', 'endDate', 'serviceId', 'institutionType', 'search'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $query = $this->buildQuery($request);

        $fileName = sprintf('rekapitulasi_pengunjung_pst_bps_%s_sd_%s.csv', $startDate, $endDate);

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM agar Excel menampilkan aksen karakter dengan benar
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header kolom CSV
            fputcsv($handle, [
                'No', 'Tanggal Kunjungan', 'Kode Antrean', 'Layanan PST', 'Loket Pelayanan',
                'Petugas Loket', 'Nama Lengkap', 'Nomor Kontak', 'Email',
                'Instansi Asal', 'Kategori Instansi', 'Rincian Kebutuhan Data',
                'Durasi Tunggu (Detik)', 'Durasi Layanan (Detik)'
            ]);

            $no = 1;
            $query->chunk(100, function ($records) use ($handle, &$no) {
                foreach ($records as $gb) {
                    fputcsv($handle, [
                        $no++,
                        $gb->queue->queue_date->format('d/m/Y'),
                        $gb->queue->ticket_code,
                        $gb->queue->service?->name ?? '-',
                        $gb->queue->counter?->name ?? '-',
                        $gb->queue->user?->name ?? '-',
                        $gb->full_name,
                        $gb->phone_number,
                        $gb->email ?? '-',
                        $gb->institution_name,
                        strtoupper($gb->institution_type),
                        $gb->data_needs_description,
                        $gb->queue->wait_duration_seconds ?? 0,
                        $gb->queue->serve_duration_seconds ?? 0,
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $records = $this->buildQuery($request)
            ->orderBy('id')
            ->get();

        $totalRecords = $records->count();

        $avgWaitSeconds = $records->avg(fn ($r) => $r->queue->wait_duration_seconds ?? 0) ?? 0;
        $avgServeSeconds = $records->avg(fn ($r) => $r->queue->serve_duration_seconds ?? 0) ?? 0;

        $avgWaitMinutes = round($avgWaitSeconds / 60, 1);
        $avgServeMinutes = round($avgServeSeconds / 60, 1);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalRecords' => $totalRecords,
            'avgWaitMinutes' => $avgWaitMinutes,
            'avgServeMinutes' => $avgServeMinutes,
            'printedAt' => now()->translatedFormat('d F Y'),
        ])->setPaper('a4', 'landscape');

        $fileName = sprintf('laporan_rekapitulasi_pst_bps_%s_sd_%s.pdf', $startDate, $endDate);

        return $pdf->download($fileName);
    }
}
