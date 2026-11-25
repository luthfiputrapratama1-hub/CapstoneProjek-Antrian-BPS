@props([
    'tone' => 'neutral',
])

@php
    $tones = [
        'neutral' => 'bg-[#EDF1F6] text-[#334155] border-[#DCE3EC]',
        'cobalt' => 'bg-blue-50 text-[#0B4F9C] border-blue-200',
        'green' => 'bg-emerald-50 text-[#15803D] border-emerald-200',
        'orange' => 'bg-orange-50 text-[#C2410C] border-orange-200',
        'teal' => 'bg-teal-50 text-[#0F766E] border-teal-200',
        'danger' => 'bg-red-50 text-red-700 border-red-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-xs font-semibold ' . ($tones[$tone] ?? $tones['neutral'])]) }}>
    {{ $slot }}
</span>
