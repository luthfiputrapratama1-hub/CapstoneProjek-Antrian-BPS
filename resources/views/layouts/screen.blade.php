@props([
    'theme' => 'light',
])
@php
    $isDark = ($screenTheme ?? $theme) === 'dark';
    $bodyClass = $isDark
        ? 'bg-[#090E17] text-slate-100 min-h-dvh flex flex-col justify-between antialiased select-none overflow-x-hidden'
        : 'bg-[#F4F6F9] text-[#0F172A] min-h-dvh flex flex-col justify-between antialiased select-none';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Layar Layanan — PST BPS Kota Tasikmalaya')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="{{ $bodyClass }}">
    @yield('content')
    @stack('scripts')
</body>
</html>
