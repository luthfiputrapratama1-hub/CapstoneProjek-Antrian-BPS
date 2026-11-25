@props([
    'labelledBy' => null,
    'label' => 'Tabel data',
])

<div tabindex="0"
     role="region"
     @if($labelledBy) aria-labelledby="{{ $labelledBy }}" @else aria-label="{{ $label }}" @endif
     {{ $attributes->merge(['class' => 'overflow-x-auto focus-visible:outline-2 focus-visible:outline-[#0B4F9C]']) }}>
    {{ $slot }}
</div>
