@props([
    'id',
    'title'       => null,
    'subtitle'    => null,
    'size'        => 'md',
    'closeOnBackdrop' => true,
    'closeFn'     => null,
    'footer'      => null,
    'headerAction' => null,
])

@php
    $sizes = [
        'sm'   => 'max-w-md',
        'md'   => 'max-w-lg',
        'lg'   => 'max-w-2xl',
        'xl'   => 'max-w-4xl',
        'full' => 'max-w-6xl',
    ];

    $sizeCls = $sizes[$size] ?? $sizes['md'];

    $closeAction = $closeFn
        ? "{$closeFn}()"
        : "document.getElementById('{$id}').classList.add('hidden')";

    $backdropAction = $closeOnBackdrop
        ? "if(event.target === this) {$closeAction}"
        : '';
@endphp

<div id="{{ $id }}"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     @if ($backdropAction) onclick="{{ $backdropAction }}" @endif>

    <div class="bg-white rounded-2xl shadow-xl w-full {{ $sizeCls }} max-h-[90vh] flex flex-col overflow-hidden">

        {{-- Header --}}
        @if ($title)
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 truncate">{{ $title }}</h3>
                    @if ($subtitle)
                        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $subtitle }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-1 shrink-0">
                    @if ($headerAction)
                        {{ $headerAction }}
                    @endif

                    <button type="button"
                            onclick="{{ $closeAction }}"
                            class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-5">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if ($footer)
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 shrink-0">
                {{ $footer }}
            </div>
        @endif

    </div>
</div>