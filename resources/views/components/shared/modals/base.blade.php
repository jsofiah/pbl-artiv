@props([
    'id',
    'title'           => null,
    'subtitle'        => null,
    'size'            => 'md',
    'closeOnBackdrop' => true,
    'closeFn'         => null,
    'footer'          => null,
    'headerAction'    => null,

    // Mode Alpine (opsional)
    'showVar'         => null,   // ex: 'showAddModal' → pakai x-show
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

    $isAlpine = $showVar !== null;

    $closeAction = $closeFn
        ? ($isAlpine ? "{$showVar} = false" : "{$closeFn}()")
        : ($isAlpine
            ? "{$showVar} = false"
            : "document.getElementById('{$id}').classList.add('hidden')");

    $backdropAction = $closeOnBackdrop
        ? ($isAlpine
            ? "@click.self=\"{$showVar} = false\""
            : "onclick=\"if(event.target === this) {$closeAction}\"")
        : '';
@endphp

<div id="{{ $id }}"
     @if ($isAlpine)
         x-show="{{ $showVar }}"
         x-cloak
     @else
         class="hidden"
     @endif
     @if ($isAlpine)
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
     @endif
     {!! $backdropAction !!}
     @if ($isAlpine)
         @keydown.escape.window="{{ $showVar }} = false"
     @endif
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="margin-top: 0;">

    <div class="bg-white rounded-2xl shadow-xl w-full {{ $sizeCls }} max-h-[90vh] flex flex-col overflow-hidden"
         @if ($isAlpine)
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-3 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         @endif>

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
                            @if ($isAlpine)
                                @click="{{ $showVar }} = false"
                            @else
                                onclick="{{ $closeAction }}"
                            @endif
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