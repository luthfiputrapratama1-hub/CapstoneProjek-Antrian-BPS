@props([
    'title' => 'BADAN PUSAT STATISTIK',
    'subtitle' => 'Pelayanan Statistik Terpadu · Kota Tasikmalaya',
    'href' => route('home'),
    'dark' => false,
    'size' => 'md',
])

@php
    $boxSize = $size === 'lg' ? 'w-12 h-12' : 'w-10 h-10';
    $titleClass = $dark ? 'text-white' : 'text-[#0F172A]';
    $subClass = $dark ? 'text-slate-300' : 'text-[#475569]';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 group focus-visible:outline-2 focus-visible:outline-offset-2 rounded-md']) }}>
    <span class="{{ $boxSize }} shrink-0 rounded-lg border {{ $dark ? 'border-white/15 bg-white/10' : 'border-[#DCE3EC] bg-white' }} flex items-center justify-center p-1.5">
        <svg viewBox="0 0 36 36" class="w-full h-full" aria-hidden="true">
            <rect x="3" y="6" width="30" height="6" rx="1.5" fill="#0B4F9C" />
            <rect x="3" y="15" width="23" height="6" rx="1.5" fill="#15803D" />
            <rect x="3" y="24" width="16" height="6" rx="1.5" fill="#EA580C" />
            <circle cx="28" cy="27" r="3" fill="{{ $dark ? '#F8FAFC' : '#0A2540' }}" />
        </svg>
    </span>
    <span class="min-w-0 leading-tight">
        <span class="block text-sm sm:text-base font-bold tracking-tight {{ $titleClass }}">{{ $title }}</span>
        @if($subtitle)
            <span class="block text-xs font-medium {{ $subClass }} truncate">{{ $subtitle }}</span>
        @endif
    </span>
</a>
