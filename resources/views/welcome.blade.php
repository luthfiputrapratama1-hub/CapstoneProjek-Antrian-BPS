@extends('layouts.portal')

@section('title', 'Sistem Informasi Antrean & Rekapitulasi PST — BPS Kota Tasikmalaya')

@section('header-actions')
    <span class="hidden md:inline-flex items-center gap-1.5 rounded-md border border-white/15 bg-white/10 px-2.5 py-1 text-xs font-medium text-slate-200 tabular-nums-strict">
        <x-icon name="clock" class="w-3.5 h-3.5 text-emerald-400" />
        <span>Jam Layanan: 08.00–15.30 WIB</span>
    </span>

    @auth
        @if(Auth::user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}"
               class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-[#0A2540] hover:bg-slate-100">
                Panel Admin
            </a>
        @else
            <a href="{{ route('counter.index') }}"
               class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-[#0A2540] hover:bg-slate-100">
                Meja Loket
            </a>
        @endif
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="rounded-md bg-red-700 hover:bg-red-800 px-3 py-1.5 text-xs font-semibold text-white">
                Keluar
            </button>
        </form>
    @else
        <a href="{{ route('login') }}"
           class="rounded-md border border-white/20 bg-white/10 hover:bg-white/20 px-3.5 py-1.5 text-xs font-semibold text-white">
            Masuk Petugas / Admin
        </a>
    @endauth
@endsection

@section('content')
    <div class="space-y-10">
        {{-- Institutional Hero --}}
        <section class="bg-white border border-[#DCE3EC] rounded-xl p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl space-y-2.5">
                <x-badge tone="cobalt">Portal Sistem Antrean Terpadu</x-badge>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#0F172A]">
                    Selamat Datang di Pelayanan Statistik Terpadu (PST)
                </h1>
                <p class="text-sm text-[#475569] leading-relaxed">
                    Sistem antrean dan buku tamu digital resmi <strong class="font-semibold text-[#0F172A]">Badan Pusat Statistik Kota Tasikmalaya</strong> untuk pengambilan tiket mandiri, layar panggilan ruang tunggu, pelayanan loket, dan rekapitulasi kunjungan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('kiosk.index') }}"
                   class="inline-flex items-center gap-2 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2.5 text-sm font-semibold text-white">
                    <x-icon name="ticket" class="w-4 h-4" />
                    <span>Buka Anjungan Kiosk</span>
                </a>
                <a href="{{ route('display.index') }}"
                   class="inline-flex items-center gap-2 rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-4 py-2.5 text-sm font-semibold text-[#0F172A]">
                    <x-icon name="tv" class="w-4 h-4" />
                    <span>Layar Display TV</span>
                </a>
            </div>
        </section>

        {{-- Zona 1: Layanan Publik & Ruang Tunggu --}}
        <section class="space-y-4" aria-labelledby="zone-public-heading">
            <div class="flex items-center justify-between border-b border-[#DCE3EC] pb-2">
                <div>
                    <h2 id="zone-public-heading" class="text-base font-bold text-[#0F172A]">Layanan Publik &amp; Ruang Tunggu</h2>
                    <p class="text-xs text-[#475569]">Perangkat akses terbuka untuk pengunjung dan monitor ruang tunggu PST</p>
                </div>
                <x-badge tone="green">Akses Publik</x-badge>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <a href="{{ route('kiosk.index') }}"
                   class="group bg-white rounded-xl border border-[#DCE3EC] hover:border-[#0B4F9C] p-6 flex flex-col justify-between transition-colors">
                    <div class="flex items-start gap-4">
                        <span class="w-11 h-11 rounded-lg bg-blue-50 border border-blue-200 text-[#0B4F9C] flex items-center justify-center shrink-0">
                            <x-icon name="ticket" class="w-5 h-5" />
                        </span>
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-[#0B4F9C]">Pengunjung PST</span>
                            <h3 class="text-lg font-bold text-[#0F172A] group-hover:text-[#0B4F9C] transition-colors">
                                Anjungan Kiosk Tiket
                            </h3>
                            <p class="text-xs text-[#475569] leading-relaxed">
                                Layar sentuh mandiri untuk memilih 4 kategori layanan statistik dan mencetak nomor antrean termal.
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 pt-3.5 border-t border-[#EDF1F6] flex items-center justify-between text-xs font-semibold text-[#0B4F9C]">
                        <span>Buka Anjungan Kiosk</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </div>
                </a>

                <a href="{{ route('display.index') }}"
                   class="group bg-white rounded-xl border border-[#DCE3EC] hover:border-[#15803D] p-6 flex flex-col justify-between transition-colors">
                    <div class="flex items-start gap-4">
                        <span class="w-11 h-11 rounded-lg bg-emerald-50 border border-emerald-200 text-[#15803D] flex items-center justify-center shrink-0">
                            <x-icon name="tv" class="w-5 h-5" />
                        </span>
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-[#15803D]">Monitor Ruang Tunggu</span>
                            <h3 class="text-lg font-bold text-[#0F172A] group-hover:text-[#15803D] transition-colors">
                                Layar Monitor TV
                            </h3>
                            <p class="text-xs text-[#475569] leading-relaxed">
                                Papan status panggilan loket realtime dengan nada panggil sintetis, riwayat panggilan, dan ringkasan tunggu.
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 pt-3.5 border-t border-[#EDF1F6] flex items-center justify-between text-xs font-semibold text-[#15803D]">
                        <span>Buka Layar Display</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </div>
                </a>
            </div>
        </section>

        {{-- Zona 2: Operasional Staf & Pimpinan --}}
        <section class="space-y-4" aria-labelledby="zone-staff-heading">
            <div class="flex items-center justify-between border-b border-[#DCE3EC] pb-2">
                <div>
                    <h2 id="zone-staff-heading" class="text-base font-bold text-[#0F172A]">Operasional Staf &amp; Pimpinan</h2>
                    <p class="text-xs text-[#475569]">Area kerja internal terotentikasi untuk petugas loket dan administrator PST</p>
                </div>
                <x-badge tone="neutral">Perlu Masuk</x-badge>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <a href="{{ route('counter.index') }}"
                   class="group bg-white rounded-xl border border-[#DCE3EC] hover:border-[#0A2540] p-5 flex items-center justify-between gap-4 transition-colors">
                    <div class="flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-lg bg-[#EDF1F6] border border-[#DCE3EC] text-[#0A2540] flex items-center justify-center shrink-0">
                            <x-icon name="monitor" class="w-5 h-5" />
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-[#0F172A]">Meja Kerja Pelayanan</h3>
                                <span class="text-[11px] font-medium text-[#64748B]">· Petugas Loket</span>
                            </div>
                            <p class="text-xs text-[#475569] mt-1 leading-relaxed">
                                Panggil antrean FIFO, recall suara, pengisian buku tamu digital UU PDP, dan penyelesaian tiket.
                            </p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-[#475569] group-hover:text-[#0A2540] shrink-0" />
                </a>

                <a href="{{ route('admin.dashboard') }}"
                   class="group bg-white rounded-xl border border-[#DCE3EC] hover:border-[#0A2540] p-5 flex items-center justify-between gap-4 transition-colors">
                    <div class="flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-lg bg-[#EDF1F6] border border-[#DCE3EC] text-[#0A2540] flex items-center justify-center shrink-0">
                            <x-icon name="chart" class="w-5 h-5" />
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-[#0F172A]">Dashboard & Rekap</h3>
                                <span class="text-[11px] font-medium text-[#64748B]">· Pimpinan &amp; Admin</span>
                            </div>
                            <p class="text-xs text-[#475569] mt-1 leading-relaxed">
                                Statistik MoM, grafik tren &amp; jam sibuk, manajemen layanan/loket/akun, serta ekspor CSV &amp; PDF kop BPS.
                            </p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-[#475569] group-hover:text-[#0A2540] shrink-0" />
                </a>
            </div>
        </section>

        {{-- 4 Pilar Layanan PST BPS --}}
        <section class="bg-white rounded-xl border border-[#DCE3EC] p-6" aria-labelledby="pillars-heading">
            <div class="mb-5">
                <h2 id="pillars-heading" class="text-base font-bold text-[#0F172A]">4 Pilar Layanan Statistik Terpadu (PST) BPS</h2>
                <p class="text-xs text-[#475569] mt-0.5">Standar Pelayanan Publik sesuai amanat UU No. 16 Tahun 1997 tentang Statistik</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-lg border border-[#DCE3EC] border-t-2 border-t-[#0B4F9C] bg-[#F8FAFC] p-4 space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-md bg-[#0B4F9C] text-white font-mono font-bold text-xs flex items-center justify-center">A</span>
                        <h3 class="text-sm font-bold text-[#0F172A]">Konsultasi Statistik</h3>
                    </div>
                    <p class="text-xs text-[#475569] leading-relaxed">
                        Konsultasi data statistik sektoral, konsep definisi, dan metodologi survei/sensus bagi peneliti dan masyarakat.
                    </p>
                </div>

                <div class="rounded-lg border border-[#DCE3EC] border-t-2 border-t-[#15803D] bg-[#F8FAFC] p-4 space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-md bg-[#15803D] text-white font-mono font-bold text-xs flex items-center justify-center">B</span>
                        <h3 class="text-sm font-bold text-[#0F172A]">Rekomendasi Statistik</h3>
                    </div>
                    <p class="text-xs text-[#475569] leading-relaxed">
                        Penerbitan surat rekomendasi kegiatan statistik sektoral (Romantik) untuk OPD dan instansi pemerintah daerah.
                    </p>
                </div>

                <div class="rounded-lg border border-[#DCE3EC] border-t-2 border-t-[#C2410C] bg-[#F8FAFC] p-4 space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-md bg-[#C2410C] text-white font-mono font-bold text-xs flex items-center justify-center">C</span>
                        <h3 class="text-sm font-bold text-[#0F172A]">Data Mikro &amp; Publikasi</h3>
                    </div>
                    <p class="text-xs text-[#475569] leading-relaxed">
                        Permohonan raw dataset mikro (Susenas, Sakernas, Sensus) serta salinan publikasi statistik resmi BPS.
                    </p>
                </div>

                <div class="rounded-lg border border-[#DCE3EC] border-t-2 border-t-[#0F766E] bg-[#F8FAFC] p-4 space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-md bg-[#0F766E] text-white font-mono font-bold text-xs flex items-center justify-center">D</span>
                        <h3 class="text-sm font-bold text-[#0F172A]">Perpustakaan Statistik</h3>
                    </div>
                    <p class="text-xs text-[#475569] leading-relaxed">
                        Ruang baca koleksi pustaka, buletin indikator strategis daerah, dan literatur statistik cetak di kantor BPS.
                    </p>
                </div>
            </div>
        </section>

        {{-- Strip Kepatuhan UU PDP --}}
        <section class="rounded-xl border border-[#DCE3EC] bg-white px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <span class="w-9 h-9 rounded-lg bg-[#EDF1F6] text-[#0B4F9C] flex items-center justify-center shrink-0">
                    <x-icon name="shield" class="w-5 h-5" />
                </span>
                <div>
                    <h2 class="text-xs font-bold text-[#0F172A]">Kepatuhan UU No. 27 Tahun 2022 (Pelindungan Data Pribadi)</h2>
                    <p class="text-xs text-[#475569] mt-0.5">Seluruh data identitas tamu di buku tamu digital dilindungi kerahasiaannya dan hanya digunakan untuk evaluasi standar pelayanan publik.</p>
                </div>
            </div>
            <span class="text-xs font-semibold text-[#64748B] shrink-0">PST BPS Kota Tasikmalaya</span>
        </section>
    </div>
@endsection
