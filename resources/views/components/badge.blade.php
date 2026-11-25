@props([
    'tone' => 'neutral',
])

@php
    $tones = [
        'neutral' => ['text' => 'text-[#475569]', 'dot' => 'bg-[#64748B]'],
        'cobalt' => ['text' => 'text-[#0B4F9C]', 'dot' => 'bg-[#0B4F9C]'],
        'green' => ['text' => 'text-[#15803D]', 'dot' => 'bg-[#15803D]'],
        'orange' => ['text' => 'text-[#C2410C]', 'dot' => 'bg-[#C2410C]'],
        'teal' => ['text' => 'text-[#0F766E]', 'dot' => 'bg-[#0F766E]'],
        'danger' => ['text' => 'text-red-700', 'dot' => 'bg-red-600'],
    ];
    $style = $tones[$tone] ?? $tones['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs font-semibold ' . $style['text']]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }} shrink-0" aria-hidden="true"></span>
    {{ $slot }}
</span>
