@extends('layouts.workspace', [
    'activeNav' => 'dashboard',
    'pageTitle' => 'Mini Dashboard Rekapitulasi PST',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@push('head')
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
@endpush

@section('content')
    <div class="space-y-6">
        {{-- Default Password Security Notice --}}
        @if($isUsingDefaultPassword)
            <div role="alert" class="rounded-xl border border-orange-200 border-l-4 border-l-[#C2410C] bg-orange-50 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <x-icon name="warning" class="w-5 h-5 text-[#C2410C] shrink-0 mt-0.5" />
                    <div>
                        <h2 class="text-sm font-bold text-[#0F172A]">Peringatan Keamanan Akun</h2>
                        <p class="text-xs text-[#475569] mt-0.5">Akun Anda masih menggunakan kata sandi bawaan sistem. Segera perbarui kata sandi untuk menjaga keamanan data operasional PST.</p>
                    </div>
                </div>
                <a href="{{ route('password.change') }}"
                   class="inline-flex items-center justify-center rounded-md bg-[#C2410C] hover:bg-orange-800 px-3.5 py-2 text-xs font-semibold text-white shrink-0">
                    Ubah Kata Sandi
                </a>
            </div>
        @endif

        {{-- Explicit Month & Year Filter --}}
        <section class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-[#0F172A]">Ringkasan Evaluasi Kinerja PST</h1>
                <p class="text-xs text-[#475569] mt-0.5">
                    Periode Aktif: <strong class="font-semibold text-[#0F172A]">{{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</strong>
                    (Pembanding: {{ $monthNames[$prevMonth] }} {{ $prevYear }})
                </p>
            </div>

            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-end gap-2.5">
                <div>
                    <label for="month" class="block text-[11px] font-semibold text-[#475569] mb-1">Bulan</label>
                    <select id="month" name="month" class="rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-semibold text-[#0F172A]">
                        @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}" {{ $selectedMonth === $num ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="year" class="block text-[11px] font-semibold text-[#475569] mb-1">Tahun</label>
                    <select id="year" name="year" class="rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs font-semibold text-[#0F172A] tabular-nums-strict">
                        @for($y = 2024; $y <= 2028; $y++)
                            <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">
                    Terapkan Filter
                </button>
            </form>
        </section>

        {{-- Row 1: 4 Structured KPI Cards --}}
        <section aria-label="Indikator Kinerja Utama" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold text-[#475569]">Total Kunjungan</span>
                        <div class="text-3xl font-bold text-[#0A2540] mt-1.5 tabular-nums-strict">{{ $totalMonth }}</div>
                    </div>
                    <span class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-200 text-[#0B4F9C] flex items-center justify-center shrink-0">
                        <x-icon name="users" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EDF1F6] flex items-center justify-between text-xs tabular-nums-strict">
                    <span class="text-[#475569]">Bulan lalu: <strong class="text-[#0F172A]">{{ $prevMonthTotal }}</strong></span>
                    <x-badge :tone="$diffTotal >= 0 ? 'green' : 'danger'">
                        {{ $diffTotal >= 0 ? 'Naik +' : 'Turun ' }}{{ $growthRate }}%
                    </x-badge>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold text-[#475569]">Selesai Dilayani</span>
                        <div class="text-3xl font-bold text-[#15803D] mt-1.5 tabular-nums-strict">{{ $completedMonth }}</div>
                    </div>
                    <span class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200 text-[#15803D] flex items-center justify-center shrink-0">
                        <x-icon name="check" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EDF1F6] grid grid-cols-3 gap-1.5 text-center text-[11px] font-semibold text-[#475569] tabular-nums-strict">
                    <div class="rounded bg-[#F4F6F9] py-1">Layani: {{ $servingMonth }}</div>
                    <div class="rounded bg-[#F4F6F9] py-1">Tunggu: {{ $waitingMonth }}</div>
                    <div class="rounded bg-red-50 text-red-700 py-1">Skip: {{ $skippedMonth }}</div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold text-[#475569]">Rerata Waktu Tunggu</span>
                        <div class="text-3xl font-bold text-[#C2410C] mt-1.5 tabular-nums-strict">
                            {{ $avgWaitMinutes }} <span class="text-sm font-medium text-[#475569]">mnt</span>
                        </div>
                    </div>
                    <span class="w-10 h-10 rounded-lg bg-orange-50 border border-orange-200 text-[#C2410C] flex items-center justify-center shrink-0">
                        <x-icon name="clock" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EDF1F6] text-xs text-[#475569]">
                    Standar BPS: <strong class="font-semibold text-[#15803D]">&lt; 15 Menit</strong>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#DCE3EC] p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold text-[#475569]">Rerata Durasi Layanan</span>
                        <div class="text-3xl font-bold text-[#0F766E] mt-1.5 tabular-nums-strict">
                            {{ $avgServeMinutes }} <span class="text-sm font-medium text-[#475569]">mnt</span>
                        </div>
                    </div>
                    <span class="w-10 h-10 rounded-lg bg-teal-50 border border-teal-200 text-[#0F766E] flex items-center justify-center shrink-0">
                        <x-icon name="chart" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EDF1F6] text-xs text-[#475569]">
                    Waktu konsultasi per pemohon
                </div>
            </div>
        </section>

        {{-- Row 2: Daily Trend & Peak Hours --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <section class="lg:col-span-2 bg-white rounded-xl border border-[#DCE3EC] p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-[#0F172A]">Tren Volume Kunjungan Harian</h2>
                        <p class="text-xs text-[#475569]">Pemohon per tanggal pada {{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</p>
                    </div>
                    <x-badge tone="cobalt">Harian</x-badge>
                </div>
                <div class="h-64 relative">
                    <canvas id="trendChart"></canvas>
                </div>
            </section>

            <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-[#0F172A]">Jam Sibuk Pelayanan</h2>
                        <p class="text-xs text-[#475569]">Distribusi waktu kedatangan (WIB)</p>
                    </div>
                    <x-badge tone="orange">Peak Hours</x-badge>
                </div>
                <div class="h-64 relative">
                    <canvas id="busyHoursChart"></canvas>
                </div>
            </section>
        </div>

        {{-- Row 3: Service Distribution & Institution Type --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                <h2 class="text-sm font-bold text-[#0F172A]">Distribusi Akses Kategori Layanan PST</h2>
                <p class="text-xs text-[#475569] mb-4">Proporsi permohonan berdasarkan 4 layanan resmi BPS</p>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="serviceChart"></canvas>
                </div>
            </section>

            <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                <h2 class="text-sm font-bold text-[#0F172A]">Proporsi Kategori Instansi Pemohon</h2>
                <p class="text-xs text-[#475569] mb-4">Segmentasi asal pengunjung (Pendidikan, Pemerintah, Swasta, LSM, Umum)</p>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="institutionChart"></canvas>
                </div>
            </section>
        </div>

        {{-- Row 4: Recent 10 Visitors Table --}}
        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <div class="p-5 border-b border-[#DCE3EC] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 id="recent-visitors-title" class="text-sm font-bold text-[#0F172A]">Daftar Tamu Terverifikasi Terakhir (Bulan Ini)</h2>
                    <p class="text-xs text-[#475569] mt-0.5">Rekaman buku tamu digital pengunjung yang selesai dilayani</p>
                </div>
                <a href="{{ route('admin.reports.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
                   class="inline-flex items-center gap-1.5 rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-3.5 py-2 text-xs font-semibold text-[#0B4F9C] shrink-0">
                    <span>Lihat Seluruh Laporan &amp; Ekspor PDF / CSV</span>
                    <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>

            <x-table-wrapper labelled-by="recent-visitors-title">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Tanggal</th>
                            <th scope="col" class="py-3 px-4">Tiket</th>
                            <th scope="col" class="py-3 px-4">Nama Pengunjung</th>
                            <th scope="col" class="py-3 px-4">Instansi Asal</th>
                            <th scope="col" class="py-3 px-4">Layanan PST</th>
                            <th scope="col" class="py-3 px-4">Durasi</th>
                            <th scope="col" class="py-3 px-4">Kebutuhan Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @forelse($recentVisitors as $v)
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3 px-4 text-xs text-[#475569] whitespace-nowrap tabular-nums-strict">{{ $v->queue->queue_date->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-[#0A2540] whitespace-nowrap tabular-nums-strict">{{ $v->queue->ticket_code }}</td>
                                <td class="py-3 px-4 font-semibold text-[#0F172A]">{{ $v->full_name }}</td>
                                <td class="py-3 px-4 text-xs text-[#475569]">{{ $v->institution_name }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <x-badge tone="cobalt">{{ $v->queue->service?->name ?? '-' }}</x-badge>
                                </td>
                                <td class="py-3 px-4 text-xs font-mono text-[#475569] whitespace-nowrap tabular-nums-strict">
                                    {{ round(($v->queue->serve_duration_seconds ?? 0) / 60, 1) }} mnt
                                </td>
                                <td class="py-3 px-4 text-xs text-[#475569] max-w-xs" title="{{ $v->data_needs_description }}">
                                    <span class="block truncate">{{ $v->data_needs_description }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-xs text-[#64748B]">Belum ada data kunjungan pada periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-wrapper>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.color = '#475569';
        Chart.defaults.font.family = "'Public Sans', 'Inter', system-ui, sans-serif";
        Chart.defaults.font.size = 11;

        const gridColor = '#E2E8F0';

        const ctxTrend = document.getElementById('trendChart')?.getContext('2d');
        if (ctxTrend) {
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: {!! json_encode($trendLabels) !!},
                    datasets: [{
                        label: 'Jumlah Kunjungan',
                        data: {!! json_encode($trendData) !!},
                        borderColor: '#0B4F9C',
                        backgroundColor: 'rgba(11, 79, 156, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.25,
                        pointRadius: 2.5,
                        pointBackgroundColor: '#0A2540',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } },
                        x: { grid: { display: false } },
                    },
                },
            });
        }

        const ctxBusy = document.getElementById('busyHoursChart')?.getContext('2d');
        if (ctxBusy) {
            new Chart(ctxBusy, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($busyHourLabels) !!},
                    datasets: [{
                        label: 'Pemohon',
                        data: {!! json_encode($busyHourData) !!},
                        backgroundColor: '#C2410C',
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } },
                        x: { grid: { display: false } },
                    },
                },
            });
        }

        const ctxService = document.getElementById('serviceChart')?.getContext('2d');
        if (ctxService) {
            new Chart(ctxService, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($serviceChartLabels) !!},
                    datasets: [{
                        data: {!! json_encode($serviceChartData) !!},
                        backgroundColor: ['#0B4F9C', '#15803D', '#C2410C', '#0F766E'],
                        borderColor: '#FFFFFF',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, font: { size: 11 } } },
                    },
                },
            });
        }

        const ctxInst = document.getElementById('institutionChart')?.getContext('2d');
        if (ctxInst) {
            new Chart(ctxInst, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($institutionChartLabels) !!},
                    datasets: [{
                        label: 'Pemohon',
                        data: {!! json_encode($institutionChartData) !!},
                        backgroundColor: ['#0B4F9C', '#15803D', '#C2410C', '#0F766E', '#475569'],
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    },
                },
            });
        }
    });
</script>
@endpush
