<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PST BPS Kota Tasikmalaya')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-[#F4F6F9] text-[#0F172A] min-h-dvh flex flex-col justify-between antialiased">
    @hasSection('hide-header')
    @else
        <header class="bg-[#0A2540] text-white border-b border-white/10 sticky top-0 z-40">
            <div class="h-1 w-full flex" aria-hidden="true">
                <span class="flex-1 bg-[#0B4F9C]"></span>
                <span class="flex-1 bg-[#15803D]"></span>
                <span class="flex-1 bg-[#EA580C]"></span>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-4">
                <x-bps-brand
                    title="Pelayanan Statistik Terpadu (PST)"
                    subtitle="Badan Pusat Statistik Kota Tasikmalaya"
                    :dark="true"
                />

                <div class="flex items-center gap-2.5">
                    @yield('header-actions')
                </div>
            </div>
        </header>
    @endif

    <main class="@yield('main-class', 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full')">
        @yield('content')
    </main>

    @hasSection('hide-footer')
    @else
        <footer class="bg-white border-t border-[#DCE3EC] py-5 px-4 sm:px-6 lg:px-8 text-xs text-[#475569]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
                <p>
                    <strong class="font-semibold text-[#0F172A]">Badan Pusat Statistik Kota Tasikmalaya</strong>
                    · Jl. Ir. H. Juanda No. 84, Sukamulya, Bungursari, Kota Tasikmalaya 46151
                </p>
                <p class="text-[#64748B]">Sistem Antrean &amp; Rekapitulasi PST © 2026</p>
            </div>
        </footer>
    @endif

    <x-toast-region />
    @stack('scripts')
</body>
</html>
