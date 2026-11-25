@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-md border border-[#DCE3EC] bg-[#F4F6F9] px-3 py-1.5 text-xs font-semibold text-[#64748B] cursor-not-allowed">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F172A] hover:bg-[#F4F6F9]">
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F172A] hover:bg-[#F4F6F9]">
                    Berikutnya
                </a>
            @else
                <span class="inline-flex items-center rounded-md border border-[#DCE3EC] bg-[#F4F6F9] px-3 py-1.5 text-xs font-semibold text-[#64748B] cursor-not-allowed">
                    Berikutnya
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between gap-4">
            <p class="text-xs text-[#475569] tabular-nums-strict">
                Menampilkan
                @if ($paginator->firstItem())
                    <span class="font-semibold text-[#0F172A]">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-[#0F172A]">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                dari <span class="font-semibold text-[#0F172A]">{{ $paginator->total() }}</span> data
            </p>

            <span class="inline-flex items-center gap-1 tabular-nums-strict">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Halaman sebelumnya"
                          class="inline-flex items-center rounded-md border border-[#DCE3EC] bg-[#F4F6F9] px-2.5 py-1.5 text-xs font-semibold text-[#64748B] cursor-not-allowed">
                        <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                       class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-2.5 py-1.5 text-xs font-semibold text-[#0F172A] hover:bg-[#EDF1F6]">
                        <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="px-2 py-1 text-xs text-[#64748B]">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex items-center rounded-md border border-[#0B4F9C] bg-[#0B4F9C] px-3 py-1.5 text-xs font-semibold text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   aria-label="Ke halaman {{ $page }}"
                                   class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-semibold text-[#334155] hover:bg-[#EDF1F6]">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                       class="inline-flex items-center rounded-md border border-[#CBD5E1] bg-white px-2.5 py-1.5 text-xs font-semibold text-[#0F172A] hover:bg-[#EDF1F6]">
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Halaman berikutnya"
                          class="inline-flex items-center rounded-md border border-[#DCE3EC] bg-[#F4F6F9] px-2.5 py-1.5 text-xs font-semibold text-[#64748B] cursor-not-allowed">
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
