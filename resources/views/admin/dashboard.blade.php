<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Dashboard Rekapitulasi - Administrator PST BPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js Dimuat Secara Lokal untuk Mendukung Lingkungan LAN Offline -->
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between selection:bg-none font-sans">
    <!-- Navbar Admin -->
    <header class="bg-blue-950 text-white shadow-md py-4 px-8 flex justify-between items-center border-b border-blue-900">
        <div class="flex items-center space-x-4">
            <a href="{{ route('home') }}" title="Kembali ke Beranda Portal PST"
                class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-900 font-black text-xl shadow hover:scale-105 transition-transform">
                BPS
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-wide">Mini Dashboard Rekapitulasi Pelayanan Statistik</h1>
                <p class="text-xs text-blue-200">Badan Pusat Statistik Kota Tasikmalaya</p>
            </div>
        </div>

        <div class="flex items-center space-x-6">
            <nav class="flex space-x-4 text-sm font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="text-emerald-400 border-b-2 border-emerald-400 pb-1">Dashboard</a>
                <a href="{{ route('admin.reports.index') }}" class="text-blue-200 hover:text-white transition pb-1">Rekap Laporan</a>
                <a href="{{ route('admin.services.index') }}" class="text-blue-200 hover:text-white transition pb-1">Layanan</a>
                <a href="{{ route('admin.counters.index') }}" class="text-blue-200 hover:text-white transition pb-1">Loket</a>
                <a href="{{ route('admin.users.index') }}" class="text-blue-200 hover:text-white transition pb-1">Pengguna</a>
                <a href="{{ route('counter.index') }}" class="text-blue-200 hover:text-white transition pb-1">Buka Loket</a>
            </nav>

            <div class="border-l border-blue-800 pl-6 flex items-center space-x-3">
                <span class="text-xs text-blue-200">Admin: <strong>{{ Auth::user()->name }}</strong></span>
                <a href="{{ route('password.change') }}" class="bg-blue-900 hover:bg-blue-800 text-white text-xs px-2.5 py-1.5 rounded-lg font-semibold transition border border-blue-700">
                    Kata Sandi
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-700 hover:bg-red-800 text-white text-xs px-3 py-1.5 rounded-lg font-bold transition shadow">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Dashboard -->
    <main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-8">
        <!-- Peringatan Keamanan Bila Masih Memakai Kata Sandi Bawaan -->
        @if($isUsingDefaultPassword)
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-2xl shadow-sm flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <h4 class="text-sm font-bold text-amber-800">Peringatan Keamanan Akun</h4>
                        <p class="text-xs text-amber-700 mt-0.5">Akun Anda saat ini masih menggunakan kata sandi bawaan sistem. Demi keamanan operasional data PST, segera perbarui kata sandi Anda.</p>
                    </div>
                </div>
                <a href="{{ route('password.change') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow shrink-0">
                    Ubah Kata Sandi
                </a>
            </div>
        @endif

        <!-- Filter Bulan dan Tahun Eksplisit -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-800">Ringkasan Evaluasi Kinerja PST</h2>
                <p class="text-slate-500 text-xs mt-0.5">
                    Periode Aktif: <strong>{{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</strong> (Bulan Sebelumnya: {{ $monthNames[$prevMonth] }} {{ $prevYear }})
                </p>
            </div>

            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center space-x-3">
                <div>
                    <label for="month" class="sr-only">Bulan</label>
                    <select id="month" name="month" class="bg-slate-50 border border-slate-300 text-slate-800 text-xs font-bold rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}" {{ $selectedMonth === $num ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="year" class="sr-only">Tahun</label>
                    <select id="year" name="year" class="bg-slate-50 border border-slate-300 text-slate-800 text-xs font-bold rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @for($y = 2024; $y <= 2028; $y++)
                            <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <button type="submit" class="bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition">
                    Terapkan Filter
                </button>
            </form>
        </div>

        <!-- Baris 1: Kartu KPI Utama & Komparasi MoM -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Kunjungan Periode -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Kunjungan</span>
                        <div class="text-4xl font-extrabold text-blue-900 mt-2">{{ $totalMonth }}</div>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 text-blue-800 rounded-2xl flex items-center justify-center text-2xl font-bold">
                        👥
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Bulan lalu: <strong>{{ $prevMonthTotal }}</strong></span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold text-[11px] {{ $diffTotal >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $diffTotal >= 0 ? '↑ +' : '↓ ' }}{{ $growthRate }}%
                    </span>
                </div>
            </div>

            <!-- Status Antrean Periode -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Selesai Dilayani</span>
                        <div class="text-4xl font-extrabold text-emerald-600 mt-2">{{ $completedMonth }}</div>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center text-2xl font-bold">
                        ✓
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-3 gap-1 text-center text-[10px] font-bold text-slate-600">
                    <div class="bg-slate-50 p-1 rounded-lg">Melayani: {{ $servingMonth }}</div>
                    <div class="bg-slate-50 p-1 rounded-lg">Tunggu: {{ $waitingMonth }}</div>
                    <div class="bg-slate-50 p-1 rounded-lg text-rose-600">Skip: {{ $skippedMonth }}</div>
                </div>
            </div>

            <!-- Rata-rata Durasi Tunggu -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Rerata Waktu Tunggu</span>
                        <div class="text-4xl font-extrabold text-amber-600 mt-2">{{ $avgWaitMinutes }} <span class="text-base font-semibold">mnt</span></div>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 text-amber-700 rounded-2xl flex items-center justify-center text-2xl font-bold">
                        ⏳
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500">
                    Standar BPS: <span class="font-bold text-emerald-700">&lt; 15 Menit</span>
                </div>
            </div>

            <!-- Rata-rata Durasi Pelayanan -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Rerata Durasi Layanan</span>
                        <div class="text-4xl font-extrabold text-indigo-600 mt-2">{{ $avgServeMinutes }} <span class="text-base font-semibold">mnt</span></div>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-2xl flex items-center justify-center text-2xl font-bold">
                        ⏱️
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500">
                    Waktu konsultasi per pemohon
                </div>
            </div>
        </div>

        <!-- Baris 2: Grafik Tren Harian & Jam Sibuk Pelayanan -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Grafik Tren Harian (2/3 Lebar) -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-base font-black text-slate-800">Tren Volume Kunjungan Harian</h3>
                        <p class="text-xs text-slate-500">Grafik jumlah pemohon per hari pada {{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</p>
                    </div>
                    <span class="text-xs font-bold bg-blue-50 text-blue-800 px-3 py-1 rounded-full">Harian</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Grafik Analisis Jam Sibuk (1/3 Lebar) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-base font-black text-slate-800">Jam Sibuk Pelayanan</h3>
                        <p class="text-xs text-slate-500">Distribusi waktu kedatangan pengunjung (WIB)</p>
                    </div>
                    <span class="text-xs font-bold bg-amber-50 text-amber-800 px-3 py-1 rounded-full">Jam Sibuk</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="busyHoursChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Baris 3: Distribusi Layanan PST & Kategori Pemohon -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Distribusi Layanan PST -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                <h3 class="text-base font-black text-slate-800 mb-1">Distribusi Akses Kategori Layanan PST</h3>
                <p class="text-xs text-slate-500 mb-4">Proporsi permohonan berdasarkan 4 layanan resmi BPS</p>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="serviceChart"></canvas>
                </div>
            </div>

            <!-- Proporsi Kategori Pemohon -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                <h3 class="text-base font-black text-slate-800 mb-1">Proporsi Kategori Instansi Pemohon</h3>
                <p class="text-xs text-slate-500 mb-4">Segmentasi asal pengunjung (Pendidikan, Pemerintah, Swasta, LSM, Umum)</p>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="institutionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Baris 4: Tabel 10 Kunjungan Terakhir Periode -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-base font-black text-slate-800">Daftar Tamu Terverifikasi Terakhir (Bulan Ini)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar buku tamu digital pengunjung yang selesai dilayani</p>
                </div>
                <a href="{{ route('admin.reports.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
                    class="text-xs font-bold text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-xl transition">
                    Lihat Seluruh Laporan & Ekspor PDF / CSV →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 border-y border-slate-200 text-xs font-bold uppercase text-slate-500 tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Tiket</th>
                            <th class="py-3 px-4">Nama Pengunjung</th>
                            <th class="py-3 px-4">Instansi Asal</th>
                            <th class="py-3 px-4">Layanan PST</th>
                            <th class="py-3 px-4">Durasi Layanan</th>
                            <th class="py-3 px-4">Kebutuhan Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentVisitors as $v)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-xs text-slate-500">{{ $v->queue->queue_date->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-blue-900">{{ $v->queue->ticket_code }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $v->full_name }}</td>
                                <td class="py-3 px-4 text-slate-600 text-xs">{{ $v->institution_name }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        {{ $v->queue->service?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs font-mono text-slate-600">{{ round(($v->queue->serve_duration_seconds ?? 0) / 60, 1) }} mnt</td>
                                <td class="py-3 px-4 text-xs text-slate-600 max-w-xs truncate">{{ $v->data_needs_description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-slate-400 text-sm">Belum ada data kunjungan pada periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-4 px-8 text-center text-xs text-slate-500">
        Sistem Informasi Antrean & Rekapitulasi Pengunjung PST BPS Kota Tasikmalaya © 2026
    </footer>

    <!-- Inisialisasi Grafik Chart.js Lokal -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Grafik Tren Harian
            const ctxTrend = document.getElementById('trendChart')?.getContext('2d');
            if (ctxTrend) {
                new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendLabels) !!},
                        datasets: [{
                            label: 'Jumlah Kunjungan',
                            data: {!! json_encode($trendData) !!},
                            borderColor: '#1d4ed8',
                            backgroundColor: 'rgba(29, 78, 216, 0.1)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                            pointBackgroundColor: '#1e40af'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 2. Grafik Jam Sibuk (Peak Hours)
            const ctxBusy = document.getElementById('busyHoursChart')?.getContext('2d');
            if (ctxBusy) {
                new Chart(ctxBusy, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($busyHourLabels) !!},
                        datasets: [{
                            label: 'Pemohon',
                            data: {!! json_encode($busyHourData) !!},
                            backgroundColor: '#f59e0b',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 3. Grafik Distribusi Layanan PST (Donut)
            const ctxService = document.getElementById('serviceChart')?.getContext('2d');
            if (ctxService) {
                new Chart(ctxService, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($serviceChartLabels) !!},
                        datasets: [{
                            data: {!! json_encode($serviceChartData) !!},
                            backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                        }
                    }
                });
            }

            // 4. Grafik Proporsi Instansi Pemohon (Bar)
            const ctxInst = document.getElementById('institutionChart')?.getContext('2d');
            if (ctxInst) {
                new Chart(ctxInst, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($institutionChartLabels) !!},
                        datasets: [{
                            label: 'Pemohon',
                            data: {!! json_encode($institutionChartData) !!},
                            backgroundColor: ['#3b82f6', '#059669', '#d97706', '#dc2626', '#64748b'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
