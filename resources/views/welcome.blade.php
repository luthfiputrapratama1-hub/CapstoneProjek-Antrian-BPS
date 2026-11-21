<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Antrean & Rekapitulasi PST - BPS Kota Tasikmalaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col justify-between selection:bg-none font-sans text-slate-800">

    <!-- Header / Navbar Portal -->
    <header class="bg-blue-950 text-white shadow-md border-b border-blue-900 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-2xl flex items-center justify-center text-blue-950 font-black text-xl sm:text-2xl shadow">
                    BPS
                </div>
                <div>
                    <h1 class="text-base sm:text-xl font-bold tracking-tight">Pelayanan Statistik Terpadu (PST)</h1>
                    <p class="text-xs text-blue-200">Badan Pusat Statistik Kota Tasikmalaya</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <div class="hidden sm:flex items-center space-x-2 bg-blue-900/60 border border-blue-800 px-3 py-1.5 rounded-full text-xs text-blue-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Layanan Buka (08.00 - 15.30 WIB)</span>
                </div>

                @auth
                    <div class="flex items-center space-x-2">
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow">
                                Panel Admin
                            </a>
                        @else
                            <a href="{{ route('counter.index') }}"
                                class="bg-blue-800 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow">
                                Meja Loket
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-700 hover:bg-red-800 text-white text-xs px-3 py-2 rounded-xl font-bold transition shadow">
                                Keluar
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="bg-blue-800 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm px-4 py-2 rounded-xl transition shadow border border-blue-700">
                        Masuk Petugas / Admin
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 flex-1 w-full space-y-12">
        <!-- Hero Section -->
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-100 text-blue-900 border border-blue-200">
                <span>🏛️</span>
                <span>Portal Sistem Antrean Terintegrasi PST</span>
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                Selamat Datang di Pelayanan Statistik Terpadu BPS Kota Tasikmalaya
            </h2>
            <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                Pilih modul layanan di bawah untuk pengambilan nomor antrean pengunjung, pemantauan status panggilan pada layar monitor TV, operasional loket petugas, maupun rekapitulasi data buku tamu digital.
            </p>
        </div>

        <!-- 4 Grid Gerbang Akses Utama -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- 1. Kiosk Tiket -->
            <a href="{{ route('kiosk.index') }}"
                class="group bg-white rounded-3xl p-6 shadow-sm hover:shadow-xl border border-slate-200 hover:border-blue-500 transition-all duration-300 flex flex-col justify-between transform hover:-translate-y-1">
                <div class="space-y-4">
                    <div class="w-14 h-14 bg-blue-100 text-blue-800 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition duration-300 shadow-inner">
                        🎫
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">Untuk Pengunjung</span>
                        <h3 class="text-lg font-black text-slate-900 mt-2 group-hover:text-blue-900 transition">
                            Anjungan Kiosk Tiket
                        </h3>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Layar sentuh mandiri bagi pengunjung untuk memilih 4 kategori layanan dan mencetak nomor antrean.
                        </p>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-blue-900 group-hover:text-blue-700">
                    <span>Buka Anjungan Kiosk</span>
                    <span class="transform group-hover:translate-x-1 transition">→</span>
                </div>
            </a>

            <!-- 2. Display TV -->
            <a href="{{ route('display.index') }}"
                class="group bg-white rounded-3xl p-6 shadow-sm hover:shadow-xl border border-slate-200 hover:border-emerald-500 transition-all duration-300 flex flex-col justify-between transform hover:-translate-y-1">
                <div class="space-y-4">
                    <div class="w-14 h-14 bg-emerald-100 text-emerald-800 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition duration-300 shadow-inner">
                        📺
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">Ruang Tunggu</span>
                        <h3 class="text-lg font-black text-slate-900 mt-2 group-hover:text-emerald-900 transition">
                            Layar Monitor TV
                        </h3>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Tampilan visual monitor panggilan loket realtime dengan pemanggil suara sintetis dan riwayat panggilan.
                        </p>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-800 group-hover:text-emerald-700">
                    <span>Buka Layar Display</span>
                    <span class="transform group-hover:translate-x-1 transition">→</span>
                </div>
            </a>

            <!-- 3. Meja Kerja Loket -->
            <a href="{{ route('counter.index') }}"
                class="group bg-white rounded-3xl p-6 shadow-sm hover:shadow-xl border border-slate-200 hover:border-indigo-500 transition-all duration-300 flex flex-col justify-between transform hover:-translate-y-1">
                <div class="space-y-4">
                    <div class="w-14 h-14 bg-indigo-100 text-indigo-800 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition duration-300 shadow-inner">
                        🖥️
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">Untuk Petugas Loket</span>
                        <h3 class="text-lg font-black text-slate-900 mt-2 group-hover:text-indigo-900 transition">
                            Meja Kerja Pelayanan
                        </h3>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Panggilan antrean (FIFO), recall suara, input buku tamu digital sesuai UU PDP, dan penyelesaian layanan.
                        </p>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-900 group-hover:text-indigo-700">
                    <span>Akses Meja Loket</span>
                    <span class="transform group-hover:translate-x-1 transition">→</span>
                </div>
            </a>

            <!-- 4. Dashboard & Rekapitulasi -->
            <a href="{{ route('admin.dashboard') }}"
                class="group bg-white rounded-3xl p-6 shadow-sm hover:shadow-xl border border-slate-200 hover:border-amber-500 transition-all duration-300 flex flex-col justify-between transform hover:-translate-y-1">
                <div class="space-y-4">
                    <div class="w-14 h-14 bg-amber-100 text-amber-800 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition duration-300 shadow-inner">
                        📊
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">Pimpinan & Admin</span>
                        <h3 class="text-lg font-black text-slate-900 mt-2 group-hover:text-amber-900 transition">
                            Dashboard & Rekap
                        </h3>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Statistik MoM, grafik tren harian, jam sibuk, kelola loket/petugas, serta ekspor CSV & PDF resmi kop BPS.
                        </p>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-900 group-hover:text-amber-700">
                    <span>Buka Panel Dashboard</span>
                    <span class="transform group-hover:translate-x-1 transition">→</span>
                </div>
            </a>
        </div>

        <!-- Seksi Informasi 4 Layanan PST BPS -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
            <div class="mb-6">
                <h3 class="text-xl font-black text-slate-900">4 Pilar Layanan Statistik Terpadu (PST) BPS</h3>
                <p class="text-xs text-slate-500 mt-1">Standar Pelayanan Publik sesuai amanat UU No. 16 Tahun 1997 tentang Statistik</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                <div class="bg-blue-50/70 border border-blue-100 rounded-2xl p-5 space-y-2">
                    <div class="flex items-center space-x-2">
                        <span class="w-8 h-8 rounded-xl bg-blue-900 text-white font-black flex items-center justify-center text-sm">A</span>
                        <h4 class="font-bold text-blue-950">Konsultasi Statistik</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Layanan konsultasi data statistik sektoral, konsep definisi, serta metodologi survei/sensus bagi mahasiswa, akademisi, dan peneliti.
                    </p>
                </div>

                <div class="bg-emerald-50/70 border border-emerald-100 rounded-2xl p-5 space-y-2">
                    <div class="flex items-center space-x-2">
                        <span class="w-8 h-8 rounded-xl bg-emerald-700 text-white font-black flex items-center justify-center text-sm">B</span>
                        <h4 class="font-bold text-emerald-950">Rekomendasi Statistik</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Layanan penerbitan surat rekomendasi kegiatan statistik sektoral (Romantik) untuk OPD dan instansi pemerintah daerah.
                    </p>
                </div>

                <div class="bg-amber-50/70 border border-amber-100 rounded-2xl p-5 space-y-2">
                    <div class="flex items-center space-x-2">
                        <span class="w-8 h-8 rounded-xl bg-amber-700 text-white font-black flex items-center justify-center text-sm">C</span>
                        <h4 class="font-bold text-amber-950">Data Mikro & Publikasi</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Layanan permohonan raw dataset mikro (Susenas, Sakernas, Sensus) dan salinan publikasi statistik resmi BPS.
                    </p>
                </div>

                <div class="bg-purple-50/70 border border-purple-100 rounded-2xl p-5 space-y-2">
                    <div class="flex items-center space-x-2">
                        <span class="w-8 h-8 rounded-xl bg-purple-700 text-white font-black flex items-center justify-center text-sm">D</span>
                        <h4 class="font-bold text-purple-950">Perpustakaan Statistik</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Ruang baca koleksi pustaka, buletin indikator strategis daerah, dan literatur statistik cetak di kantor BPS.
                    </p>
                </div>
            </div>
        </div>

        <!-- Jaminan Privasi UU PDP & Keamanan Data -->
        <div class="bg-slate-100 rounded-2xl p-4 sm:p-6 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-600">
            <div class="flex items-center space-x-3">
                <span class="text-3xl shrink-0">🛡️</span>
                <div>
                    <h5 class="font-bold text-slate-800">Kepatuhan UU No. 27 Tahun 2022 (Pelindungan Data Pribadi)</h5>
                    <p class="mt-0.5">Seluruh data identitas tamu yang dicatatkan di buku tamu digital dilindungi kerahasiaannya dan hanya digunakan untuk keperluan evaluasi standar pelayanan publik.</p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <span class="text-[11px] font-semibold text-slate-500">PST BPS Kota Tasikmalaya</span>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500 space-y-2">
        <p><strong>Badan Pusat Statistik Kota Tasikmalaya</strong> — Jl. Ir. H. Juanda No. 84, Sukamulya, Bungursari, Kota Tasikmalaya, Jawa Barat 46151</p>
        <p>Sistem Informasi Antrean & Rekapitulasi Buku Tamu PST © 2026. Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>
