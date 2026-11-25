@props([
    'name',
    'decorative' => true,
])

@php
    $paths = [
        'ticket' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5A1.5 1.5 0 0 1 5.25 6h13.5a1.5 1.5 0 0 1 1.5 1.5v2.25a2.25 2.25 0 0 0 0 4.5v2.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-2.25a2.25 2.25 0 0 0 0-4.5V7.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 6v12" />',
        'tv' => '<rect x="3" y="4.5" width="18" height="12.5" rx="1.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 20h7.5M12 17v3" />',
        'monitor' => '<rect x="3" y="4" width="18" height="12" rx="1.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 20h9M12 16v4M7.5 10h4.5M7.5 13h2.5" />',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M6.75 16.5v-5.25M12 16.5v-9M17.25 16.5v-12" />',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.25 4.5 6.5v5.25c0 4.7 3.2 8.85 7.5 9.95 4.3-1.1 7.5-5.25 7.5-9.95V6.5L12 3.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m9.25 12 1.85 1.85 3.65-3.7" />',
        'clock' => '<circle cx="12" cy="12" r="8.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 1.75" />',
        'volume-on' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5.25 6.75 9H3.75v6h3L11 18.75V5.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15.25 8.75a4.75 4.75 0 0 1 0 6.5M18 6.25a8.25 8.25 0 0 1 0 11.5" />',
        'volume-off' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5.25 6.75 9H3.75v6h3L11 18.75V5.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m15.5 9.5 5 5m0-5-5 5" />',
        'search' => '<circle cx="10.75" cy="10.75" r="6.75" /><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 19.5-3.75-3.75" />',
        'download' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v10.5m0 0-4-4m4 4 4-4M4.5 18.75h15" />',
        'document' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6.75L18.75 8.25v12a1.5 1.5 0 0 1-1.5 1.5H7.5a1.5 1.5 0 0 1-1.5-1.5v-15a1.5 1.5 0 0 1 1.5-1.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3.75v4.5h4.5M9 12.75h6M9 16.25h4.5" />',
        'plus' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />',
        'edit' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.5a2.12 2.12 0 1 1 3 3L8.25 18.75l-4 1 1-4L16.5 4.5Z" />',
        'lock' => '<rect x="5" y="10.5" width="14" height="9.75" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 10.5V7.75a3.75 3.75 0 1 1 7.5 0v2.75" />',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="m4.75 12.5 4.5 4.5 10-10" />',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 2.75 19.25h18.5L12 3.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v4.25M12 16.75h.01" />',
        'close' => '<path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />',
        'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />',
        'arrow-right' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0-5.25-5.25M19.5 12l-5.25 5.25" />',
        'arrow-left' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0 5.25-5.25M4.5 12l5.25 5.25" />',
        'arrow-up' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0-5.25 5.25M12 4.5l5.25 5.25" />',
        'arrow-down' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0 5.25-5.25M12 19.5l-5.25-5.25" />',
        'refresh' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12a7.5 7.5 0 1 1-2.2-5.3L19.5 8.75M19.5 4.5v4.25h-4.25" />',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 19.25v-1.5a3.75 3.75 0 0 0-3.75-3.75h-4.5A3.75 3.75 0 0 0 4 17.75v1.5M10 10.5a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM20 19.25v-1.25a3.25 3.25 0 0 0-2.5-3.16M15.75 4.2a3.25 3.25 0 0 1 0 6.1" />',
        'eye' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6.25 9.5-6.25S21.5 12 21.5 12s-3.5 6.25-9.5 6.25S2.5 12 2.5 12Z" /><circle cx="12" cy="12" r="2.75" />',
        'eye-off' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.5 3.5 20.5 20.5M9.9 5.95A9.7 9.7 0 0 1 12 5.75c6 0 9.5 6.25 9.5 6.25a16.8 16.8 0 0 1-3.3 4.05M6.35 7.6C4.05 9.25 2.5 12 2.5 12s3.5 6.25 9.5 6.25c1.55 0 2.95-.4 4.2-1.05" />',
        'sliders' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5h15M4.5 16.5h15M8.5 5v5M15.5 14v5" />',
        'megaphone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5h3.75l8.25-4.5v12l-8.25-4.5H4.5v-3ZM8.25 13.5l1.5 5.25" />',
    ];
    $svgBody = $paths[$name] ?? '<circle cx="12" cy="12" r="8" />';
    $hasSizeClass = (bool) preg_match('/\b(?:w|h|size)-/', (string) $attributes->get('class'));
@endphp

<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
     @if($decorative) aria-hidden="true" @endif
     {{ $attributes->class(['shrink-0', 'w-5 h-5' => !$hasSizeClass]) }}>
    {!! $svgBody !!}
</svg>
