@props([
    'id',
    'title',
    'subtitle' => null,
    'maxWidth' => 'max-w-lg',
    'resetOnClose' => false,
])

<dialog id="{{ $id }}"
        closedby="any"
        aria-labelledby="{{ $id }}-title"
        @if($resetOnClose) data-reset-on-close="true" @endif
        {{ $attributes->merge(['class' => "pst-dialog {$maxWidth}"]) }}>
    <div class="flex items-start justify-between gap-4 border-b border-[#DCE3EC] px-6 py-4">
        <div>
            <h1 id="{{ $id }}-title" class="text-base font-bold text-[#0F172A]">{{ $title }}</h1>
            @if($subtitle)
                <p class="mt-0.5 text-xs text-[#475569]">{{ $subtitle }}</p>
            @endif
        </div>
        <button type="button"
                data-dialog-close="{{ $id }}"
                aria-label="Tutup dialog"
                class="rounded-md p-1.5 text-[#475569] hover:bg-[#EDF1F6] hover:text-[#0F172A]">
            <x-icon name="close" class="w-4 h-4" />
        </button>
    </div>

    <div class="px-6 py-5">
        {{ $slot }}
    </div>
</dialog>
