@extends('layouts.workspace', [
    'activeNav' => 'reports',
    'pageTitle' => 'Rekapitulasi Kunjungan Buku Tamu PST',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@php
    $institutionLabels = [
        'pendidikan' => 'Pendidikan / Riset',
        'pemerintah' => 'Instansi Pemerintah',
        'swasta' => 'Sektor Swasta',
        'lsm' => 'LSM / Media',
        'umum' => 'Masyarakat Umum',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
            <search>
                <form action="{{ route('admin.reports.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3.5">
                        <div>
                            <label for="start_date" class="block text-xs font-semibold text-[#0F172A] mb-1">Tanggal Mulai</label>
                            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A] tabular-nums-strict">
                        </div>

                        <div>
                            <label for="end_date" class="block text-xs font-semibold text-[#0F172A] mb-1">Tanggal Selesai</label>
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A] tabular-nums-strict">
                        </div>

                        <div>
                            <label for="service_id" class="block text-xs font-semibold text-[#0F172A] mb-1">Layanan PST</label>
                            <select id="service_id" name="service_id"
                                    class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A]">
                                <option value="">Semua Layanan</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}" {{ $serviceId == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }} ({{ $s->code_prefix }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="institution_type" class="block text-xs font-semibold text-[#0F172A] mb-1">Kategori Instansi</label>
                            <select id="institution_type" name="institution_type"
                                    class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A]">
                                <option value="">Semua Kategori</option>
                                @foreach($institutionLabels as $key => $label)
                                    <option value="{{ $key }}" {{ $institutionType == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-xs font-semibold text-[#0F172A] mb-1">Cari Nama / Instansi / Tiket</label>
                            <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Ketik kata kunci..."
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-xs text-[#0F172A]">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-[#EDF1F6]">
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">
                                <x-icon name="search" class="w-3.5 h-3.5" />
                                <span>Terapkan Filter</span>
                            </button>
                            @if($search || $serviceId || $institutionType || $startDate != now()->startOfMonth()->toDateString() || $endDate != now()->toDateString())
                                <a href="{{ route('admin.reports.index') }}"
                                   class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-3.5 py-2 text-xs font-semibold text-[#334155]">
                                    Reset
                                </a>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B] mr-1">Ekspor Data:</span>
                            <a href="{{ route('admin.reports.exportCsv', request()->query()) }}"
                               title="Unduh format spreadsheet CSV dengan UTF-8 BOM"
                               class="inline-flex items-center gap-1.5 rounded-md bg-[#15803D] hover:bg-emerald-800 px-3.5 py-2 text-xs font-semibold text-white">
                                <x-icon name="download" class="w-3.5 h-3.5" />
                                <span>Ekspor CSV</span>
                            </a>
                            <a href="{{ route('admin.reports.exportPdf', request()->query()) }}"
                               target="_blank"
                               title="Cetak/Unduh dokumen resmi PDF ber-kop BPS"
                               class="inline-flex items-center gap-1.5 rounded-md bg-red-700 hover:bg-red-800 px-3.5 py-2 text-xs font-semibold text-white">
                                <x-icon name="document" class="w-3.5 h-3.5" />
                                <span>Ekspor PDF Resmi</span>
                            </a>
                        </div>
                    </div>
                </form>
            </search>
        </section>

        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <div class="p-5 border-b border-[#DCE3EC] flex items-center justify-between gap-3">
                <h1 id="reports-table-title" class="text-base font-bold text-[#0F172A] tabular-nums-strict">
                    Daftar Pengunjung Terverifikasi (Total: {{ $visitors->total() }} Data)
                </h1>
            </div>

            <x-table-wrapper labelled-by="reports-table-title">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Tanggal</th>
                            <th scope="col" class="py-3 px-4">Tiket</th>
                            <th scope="col" class="py-3 px-4">Layanan</th>
                            <th scope="col" class="py-3 px-4">Nama Lengkap</th>
                            <th scope="col" class="py-3 px-4">Kontak</th>
                            <th scope="col" class="py-3 px-4">Instansi Asal</th>
                            <th scope="col" class="py-3 px-4">Kategori</th>
                            <th scope="col" class="py-3 px-4">Tunggu / Layanan</th>
                            <th scope="col" class="py-3 px-4">Kebutuhan Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @forelse($visitors as $v)
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3 px-4 text-xs text-[#475569] whitespace-nowrap tabular-nums-strict">{{ $v->queue->queue_date->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-[#0A2540] whitespace-nowrap tabular-nums-strict">{{ $v->queue->ticket_code }}</td>
                                <td class="py-3 px-4 text-xs font-medium text-[#0F172A]">{{ $v->queue->service?->name ?? '-' }}</td>
                                <td class="py-3 px-4 font-semibold text-[#0F172A]">{{ $v->full_name }}</td>
                                <td class="py-3 px-4 text-xs text-[#475569] tabular-nums-strict">{{ $v->phone_number }}</td>
                                <td class="py-3 px-4 text-xs text-[#334155]">{{ $v->institution_name }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <x-badge tone="neutral">{{ $institutionLabels[$v->institution_type] ?? $v->institution_type }}</x-badge>
                                </td>
                                <td class="py-3 px-4 text-xs font-mono text-[#475569] whitespace-nowrap tabular-nums-strict">
                                    {{ round(($v->queue->wait_duration_seconds ?? 0) / 60, 1) }}m /
                                    {{ round(($v->queue->serve_duration_seconds ?? 0) / 60, 1) }}m
                                </td>
                                <td class="py-3 px-4 text-xs text-[#475569] max-w-xs" title="{{ $v->data_needs_description }}">
                                    <span class="block truncate">{{ $v->data_needs_description }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-xs text-[#64748B]">Tidak ada data kunjungan sesuai filter yang dipilih</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-wrapper>

            @if($visitors->hasPages())
                <div class="p-4 border-t border-[#DCE3EC] bg-[#F8FAFC]">
                    {{ $visitors->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
