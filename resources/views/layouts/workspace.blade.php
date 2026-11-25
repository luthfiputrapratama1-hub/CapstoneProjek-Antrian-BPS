@php
    $user = Auth::user();
    $isAdmin = $user && $user->role === 'admin';
    $navItems = $isAdmin
        ? [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['key' => 'reports', 'label' => 'Rekap Laporan', 'route' => 'admin.reports.index'],
            ['key' => 'services', 'label' => 'Layanan', 'route' => 'admin.services.index'],
            ['key' => 'counters', 'label' => 'Loket', 'route' => 'admin.counters.index'],
            ['key' => 'users', 'label' => 'Pengguna', 'route' => 'admin.users.index'],
            ['key' => 'loket', 'label' => 'Meja Loket', 'route' => 'counter.index'],
        ]
        : [
            ['key' => 'loket', 'label' => 'Meja Loket', 'route' => 'counter.index'],
        ];
    $currentNav = $activeNav ?? '';
@endphp
<!DOCTYPE html>
<html lang="id" class="workspace-html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($pageTitle ?? 'Workspace') . ' — PST BPS Kota Tasikmalaya')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-[#F4F6F9] text-[#0F172A] min-h-dvh flex flex-col justify-between antialiased">
    <header class="bg-[#0A2540] text-white border-b border-white/10 sticky top-0 z-40">
        <div class="h-1 w-full flex" aria-hidden="true">
            <span class="flex-1 bg-[#0B4F9C]"></span>
            <span class="flex-1 bg-[#15803D]"></span>
            <span class="flex-1 bg-[#EA580C]"></span>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="shrink-0">
                <x-bps-brand
                    title="PST BPS"
                    subtitle="Kota Tasikmalaya"
                    :href="$isAdmin ? route('admin.dashboard') : route('counter.index')"
                    :dark="true"
                />
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <nav aria-label="Navigasi Utama" class="relative flex items-center shrink-0">
                    <button id="workspace-nav-toggle"
                            type="button"
                            aria-expanded="false"
                            aria-controls="workspace-nav-list"
                            class="lg:hidden inline-flex items-center gap-1.5 rounded-md border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20">
                        <x-icon name="menu" class="w-4 h-4" />
                        <span>Menu</span>
                    </button>

                    <ul id="workspace-nav-list"
                        class="hidden lg:flex flex-col lg:flex-row lg:items-center gap-1 lg:gap-1 absolute lg:static right-0 top-full mt-2 lg:mt-0 w-64 lg:w-auto rounded-lg lg:rounded-none border lg:border-0 border-[#1E3A5F] bg-[#0A2540] p-2 lg:p-0 shadow-lg lg:shadow-none z-50">
                        @foreach($navItems as $item)
                            @php $isCurrent = $currentNav === $item['key']; @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   @if($isCurrent) aria-current="page" @endif
                                   class="block rounded-md px-2.5 xl:px-3 py-1.5 text-xs font-semibold transition-colors whitespace-nowrap {{ $isCurrent ? 'bg-white text-[#0A2540]' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach

                        <li class="lg:hidden border-t border-white/15 mt-1 pt-2 px-1 flex flex-col gap-1.5">
                            <div class="text-[11px] text-slate-300 px-2">
                                <span class="font-semibold text-white">{{ $user?->name }}</span>
                                <span class="uppercase text-slate-400">({{ $user?->role }})</span>
                            </div>
                            <a href="{{ route('password.change') }}"
                               class="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-xs font-semibold text-slate-200 hover:bg-white/10 hover:text-white">
                                <x-icon name="lock" class="w-3.5 h-3.5" />
                                <span>Ubah Kata Sandi</span>
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left rounded-md bg-red-700/90 hover:bg-red-700 px-2.5 py-1.5 text-xs font-semibold text-white">
                                    Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <div class="hidden lg:flex items-center gap-2.5 border-l border-white/15 pl-3 shrink-0">
                    <div class="text-right leading-tight whitespace-nowrap">
                        <div class="text-xs font-semibold text-white">{{ $user?->name }}</div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-300">{{ $user?->role }}</div>
                    </div>
                    <a href="{{ route('password.change') }}"
                       class="inline-flex items-center gap-1 rounded-md border border-white/20 bg-white/10 hover:bg-white/20 px-2.5 py-1.5 text-xs font-semibold text-white whitespace-nowrap">
                        <x-icon name="lock" class="w-3.5 h-3.5" />
                        <span>Kata Sandi</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="rounded-md bg-red-700 hover:bg-red-800 px-3 py-1.5 text-xs font-semibold text-white whitespace-nowrap">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 flex-1 w-full">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-[#DCE3EC] py-4 px-4 sm:px-6 lg:px-8 text-xs text-[#64748B]">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <span>Sistem Informasi Antrean &amp; Rekapitulasi Pengunjung PST BPS Kota Tasikmalaya © 2026</span>
            <span>Kepatuhan UU No. 27 Tahun 2022 (UU PDP)</span>
        </div>
    </footer>

    <x-confirm-dialog />
    <x-toast-region />
    @stack('scripts')
</body>
</html>
