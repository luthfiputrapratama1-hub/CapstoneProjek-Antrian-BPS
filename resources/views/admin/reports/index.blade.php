<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Kunjungan Pengunjung - PST BPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between selection:bg-none">
    <!-- Navbar Admin -->
    <header class="bg-blue-950 text-white shadow-md py-4 px-8 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="{{ route('home') }}" title="Kembali ke Beranda Portal PST"
                class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-blue-900 font-black text-xl shadow hover:scale-105 transition-transform">
                BPS
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-wide">Rekapitulasi Kunjungan Buku Tamu PST</h1>
                <p class="text-xs text-blue-200">Badan Pusat Statistik Kota Tasikmalaya</p>
            </div>
        </div>

        <div class="flex items-center space-x-6">
            <nav class="flex space-x-4 text-sm font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="text-blue-200 hover:text-white transition pb-1">Dashboard</a>
                <a href="{{ route('admin.reports.index') }}" class="text-emerald-400 border-b-2 border-emerald-400 pb-1">Rekap Laporan</a>
                <a href="{{ route('admin.services.index') }}" class="text-blue-200 hover:text-white transition pb-1">Layanan</a>
                <a href="{{ route('admin.counters.index') }}" class="text-blue-200 hover:text-white transition pb-1">Loket</a>
                <a href="{{ route('admin.users.index') }}" class="text-blue-200 hover:text-white transition pb-1">Pengguna</a>
                <a href="{{ route('counter.index') }}" class="text-blue-200 hover:text-white transition pb-1">Buka Loket</a>
            </nav>

            <div class="border-l border-blue-800 pl-6 flex items-center space-x-3">
                <span class="text-xs text-blue-200">Admin: <strong>{{ Auth::user()->name }}</strong></span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-700 hover:bg-red-800 text-white text-xs px-3 py-1.5 rounded-lg font-bold transition shadow">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
        <!-- Form Filter & Tombol Ekspor -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="start_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="end_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="service_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Layanan PST</label>
                        <select id="service_id" name="service_id"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option value="">-- Semua Layanan --</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}" {{ $serviceId == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} ({{ $s->code_prefix }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="institution_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Kategori Instansi</label>
                        <select id="institution_type" name="institution_type"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option value="">-- Semua Kategori --</option>
                            <option value="pendidikan" {{ $institutionType == 'pendidikan' ? 'selected' : '' }}>Pendidikan / Mahasiswa</option>
                            <option value="pemerintah" {{ $institutionType == 'pemerintah' ? 'selected' : '' }}>Instansi Pemerintah</option>
                            <option value="swasta" {{ $institutionType == 'swasta' ? 'selected' : '' }}>Sektor Swasta</option>
                            <option value="lsm" {{ $institutionType == 'lsm' ? 'selected' : '' }}>LSM / Media</option>
                            <option value="umum" {{ $institutionType == 'umum' ? 'selected' : '' }}>Masyarakat Umum</option>
                        </select>
                    </div>

                    <div>
                        <label for="search" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Cari Nama / Instansi / Tiket</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Ketik kata kunci..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <div class="flex items-center space-x-2">
                        <button type="submit"
                            class="bg-blue-900 hover:bg-blue-800 text-white font-bold py-2.5 px-6 rounded-xl transition text-sm shadow flex items-center space-x-2">
                            <span>🔍</span>
                            <span>Terapkan Filter</span>
                        </button>
                        @if($search || $serviceId || $institutionType || $startDate != now()->startOfMonth()->toDateString() || $endDate != now()->toDateString())
                            <a href="{{ route('admin.reports.index') }}"
                                class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2.5 px-4 rounded-xl transition text-sm">
                                Reset
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center space-x-3">
                        <span class="text-xs font-bold uppercase text-slate-400">Ekspor Data:</span>
                        <a href="{{ route('admin.reports.exportCsv', request()->query()) }}"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl transition text-sm shadow flex items-center space-x-1.5"
                            title="Unduh format spreadsheet CSV dengan UTF-8 BOM">
                            <span>📥</span>
                            <span>Ekspor CSV</span>
                        </a>
                        <a href="{{ route('admin.reports.exportPdf', request()->query()) }}"
                            class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 px-4 rounded-xl transition text-sm shadow flex items-center space-x-1.5"
                            title="Cetak/Unduh dokumen resmi PDF ber-kop BPS"
                            target="_blank">
                            <span>📄</span>
                            <span>Ekspor PDF Resmi</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Rekapitulasi -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-lg font-black text-slate-800">
                    Daftar Pengunjung Terverifikasi (Total: {{ $visitors->total() }} Data)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500 tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Tiket</th>
                            <th class="py-3 px-4">Layanan</th>
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">Kontak</th>
                            <th class="py-3 px-4">Instansi Asal</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Tunggu / Layanan</th>
                            <th class="py-3 px-4">Kebutuhan Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($visitors as $v)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-xs text-slate-500 whitespace-nowrap">{{ $v->queue->queue_date->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-blue-900">{{ $v->queue->ticket_code }}</td>
                                <td class="py-3 px-4 text-xs font-semibold text-slate-700">{{ $v->queue->service?->name ?? '-' }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $v->full_name }}</td>
                                <td class="py-3 px-4 text-xs text-slate-600">{{ $v->phone_number }}</td>
                                <td class="py-3 px-4 text-xs text-slate-700">{{ $v->institution_name }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-slate-100 text-slate-700 border">
                                        {{ $v->institution_type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-600 whitespace-nowrap">
                                    {{ round(($v->queue->wait_duration_seconds ?? 0) / 60, 1) }}m /
                                    {{ round(($v->queue->serve_duration_seconds ?? 0) / 60, 1) }}m
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-600 max-w-xs truncate">{{ $v->data_needs_description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400 text-sm">Tidak ada data kunjungan sesuai filter yang dipilih</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($visitors->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $visitors->links() }}
                </div>
            @endif
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-4 px-8 text-center text-xs text-slate-500">
        Sistem Informasi Antrean & Rekapitulasi Pengunjung PST BPS Kota Tasikmalaya © 2026
    </footer>
</body>
</html>
