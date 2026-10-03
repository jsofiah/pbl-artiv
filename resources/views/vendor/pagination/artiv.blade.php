@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2 mt-8" aria-label="Pagination">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-4 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed">
                Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 hover:border-[#6D28D9] hover:text-[#6D28D9]">
                Sebelumnya
            </a>
        @endif

        {{-- Nomor halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-4 py-2 text-gray-400">
                    {{ $element }}
                </span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-4 py-2 rounded-lg bg-[#6D28D9] text-white">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 hover:border-[#6D28D9] hover:text-[#6D28D9]">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 hover:border-[#6D28D9] hover:text-[#6D28D9]">
                Berikutnya
            </a>
        @else
            <span class="px-4 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed">
                Berikutnya
            </span>
        @endif

    </nav>
@endif
