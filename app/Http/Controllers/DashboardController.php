<?php

namespace App\Http\Controllers;

use App\Models\GuestBook;
use App\Models\Queue;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        // 1. Filter Bulan dan Tahun Eksplisit
        $selectedMonth = (int) $request->get('month', now()->month);
        $selectedYear = (int) $request->get('year', now()->year);

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = now()->month;
        }
        if ($selectedYear < 2020 || $selectedYear > 2035) {
            $selectedYear = now()->year;
        }

        // Hitung Periode Bulan Sebelumnya (Month-over-Month / MoM)
        $prevCarbon = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth();
        $prevMonth = $prevCarbon->month;
        $prevYear = $prevCarbon->year;

        // Pengecekan keamanan: apakah user admin masih memakai password default
        $isUsingDefaultPassword = Hash::check('admin123', Auth::user()?->password ?? '');

        // 2. Metrik KPI Utama Periode Terpilih
        $monthQuery = Queue::whereMonth('queue_date', $selectedMonth)
            ->whereYear('queue_date', $selectedYear);

        $totalMonth = (clone $monthQuery)->count();
        $totalToday = Queue::whereDate('queue_date', $today)->count();

        // Komparasi dengan Bulan Sebelumnya
        $prevMonthTotal = Queue::whereMonth('queue_date', $prevMonth)
            ->whereYear('queue_date', $prevYear)
            ->count();

        $diffTotal = $totalMonth - $prevMonthTotal;
        $growthRate = $prevMonthTotal > 0
            ? round((($totalMonth - $prevMonthTotal) / $prevMonthTotal) * 100, 1)
            : ($totalMonth > 0 ? 100.0 : 0.0);

        // Breakdown Status Antrean di Periode Terpilih
        $completedMonth = (clone $monthQuery)->where('status', 'completed')->count();
        $servingMonth = (clone $monthQuery)->whereIn('status', ['calling', 'serving'])->count();
        $waitingMonth = (clone $monthQuery)->where('status', 'waiting')->count();
        $skippedMonth = (clone $monthQuery)->where('status', 'skipped')->count();

        // Rata-rata Durasi Menunggu & Layanan (dalam Menit) Periode Terpilih
        $avgWaitSeconds = (clone $monthQuery)
            ->whereNotNull('wait_duration_seconds')
            ->avg('wait_duration_seconds') ?? 0;
        $avgServeSeconds = (clone $monthQuery)
            ->whereNotNull('serve_duration_seconds')
            ->avg('serve_duration_seconds') ?? 0;

        $avgWaitMinutes = round($avgWaitSeconds / 60, 1);
        $avgServeMinutes = round($avgServeSeconds / 60, 1);

        // 3. Data Grafik A: Tren Kunjungan Harian pada Bulan Terpilih
        $daysInMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->daysInMonth;
        $dailyCounts = (clone $monthQuery)
            ->selectRaw('queue_date, count(*) as count')
            ->groupBy('queue_date')
            ->pluck('count', 'queue_date')
            ->all();

        $trendLabels = [];
        $trendData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $d);
            $trendLabels[] = sprintf('%02d', $d);
            $trendData[] = $dailyCounts[$dateStr] ?? 0;
        }

        // 4. Data Grafik B: Distribusi Kategori Layanan PST
        $services = Service::withCount(['queues' => function ($q) use ($selectedMonth, $selectedYear) {
            $q->whereMonth('queue_date', $selectedMonth)->whereYear('queue_date', $selectedYear);
        }])->orderBy('order_priority')->get();

        $serviceChartLabels = $services->pluck('name')->all();
        $serviceChartData = $services->pluck('queues_count')->all();

        // 5. Data Grafik C: Proporsi Kategori Instansi Pemohon
        $institutionRaw = GuestBook::selectRaw('institution_type, count(*) as count')
            ->whereHas('queue', function ($q) use ($selectedMonth, $selectedYear) {
                $q->whereMonth('queue_date', $selectedMonth)->whereYear('queue_date', $selectedYear);
            })
            ->groupBy('institution_type')
            ->pluck('count', 'institution_type')
            ->all();

        $institutionCategories = [
            'pendidikan' => 'Pendidikan / Riset',
            'pemerintah' => 'Instansi Pemerintah',
            'swasta' => 'Sektor Swasta',
            'lsm' => 'LSM / Media',
            'umum' => 'Masyarakat Umum',
        ];

        $institutionChartLabels = [];
        $institutionChartData = [];
        foreach ($institutionCategories as $key => $label) {
            $institutionChartLabels[] = $label;
            $institutionChartData[] = $institutionRaw[$key] ?? 0;
        }

        // 6. Data Grafik D: Analisis Jam Sibuk Pelayanan (Peak Hours: 08.00 - 16.00 WIB)
        // Ambil data antrean pada periode ini dan kelompokkan per jam (PHP grouping agar kompatibel SQLite & MySQL)
        $allPeriodQueues = (clone $monthQuery)->select('created_at')->get();
        $hourlyDistribution = [
            '08:00' => 0, '09:00' => 0, '10:00' => 0, '11:00' => 0,
            '12:00' => 0, '13:00' => 0, '14:00' => 0, '15:00' => 0,
        ];

        foreach ($allPeriodQueues as $q) {
            $hour = $q->created_at->format('H');
            $slot = sprintf('%02d:00', (int)$hour);
            if (isset($hourlyDistribution[$slot])) {
                $hourlyDistribution[$slot]++;
            }
        }

        $busyHourLabels = array_keys($hourlyDistribution);
        $busyHourData = array_values($hourlyDistribution);

        // 7. 10 Kunjungan Terakhir Terverifikasi (Eager Loading Mencegah N+1)
        $recentVisitors = GuestBook::with(['queue.service', 'queue.counter', 'queue.user'])
            ->whereHas('queue', function ($q) use ($selectedMonth, $selectedYear) {
                $q->whereMonth('queue_date', $selectedMonth)->whereYear('queue_date', $selectedYear);
            })
            ->orderByDesc('id')
            ->take(10)
            ->get();

        // Daftar Nama Bulan Bahasa Indonesia
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('admin.dashboard', compact(
            'selectedMonth', 'selectedYear', 'monthNames', 'prevMonth', 'prevYear',
            'totalMonth', 'totalToday', 'prevMonthTotal', 'diffTotal', 'growthRate',
            'completedMonth', 'servingMonth', 'waitingMonth', 'skippedMonth',
            'avgWaitMinutes', 'avgServeMinutes',
            'trendLabels', 'trendData',
            'serviceChartLabels', 'serviceChartData', 'services',
            'institutionChartLabels', 'institutionChartData', 'institutionCategories', 'institutionRaw',
            'busyHourLabels', 'busyHourData',
            'recentVisitors', 'isUsingDefaultPassword'
        ));
    }
}
