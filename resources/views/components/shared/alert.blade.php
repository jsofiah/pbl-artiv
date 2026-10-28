@props([
    'type'      => 'info',
    'message'   => null,
    'dismissible' => true,
    'autoHide'  => false,
    'duration'  => 3000,
])

@php
    $styles = match ($type) {
        'success' => [
            'bg'   => 'bg-lime-50 border-lime-200',
            'text' => 'text-lime-800',
            'icon' => 'text-lime-600',
            'svg'  => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'error' => [
            'bg'   => 'bg-red-50 border-red-200',
            'text' => 'text-red-800',
            'icon' => 'text-red-600',
            'svg'  => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        ],
        'warning' => [
            'bg'   => 'bg-amber-50 border-amber-200',
            'text' => 'text-amber-800',
            'icon' => 'text-amber-600',
            'svg'  => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        ],
        default => [
            'bg'   => 'bg-blue-50 border-blue-200',
            'text' => 'text-blue-800',
            'icon' => 'text-blue-600',
            'svg'  => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
        ],
    };
@endphp

<div
    x-data="{
        show: true,
        init() {
            @if ($autoHide)
                setTimeout(() => { this.show = false }, {{ $duration }});
            @endif
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="mb-4 px-4 py-3 rounded-2xl border {{ $styles['bg'] }} flex items-start gap-3"
>
    <svg class="w-5 h-5 shrink-0 mt-0.5 {{ $styles['icon'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $styles['svg'] }}"/>
    </svg>

    <div class="flex-1 min-w-0 {{ $styles['text'] }}">
        {{ $message ?? $slot }}
    </div>

    @if ($dismissible)
        <button type="button" @click="show = false"
                class="shrink-0 {{ $styles['icon'] }} hover:opacity-70 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>