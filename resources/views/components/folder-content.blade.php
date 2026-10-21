@props(['size' => 'sm'])

@php
    $cardSizeClasses = [
        'xxs' => 'w-32 h-24 md:w-40 md:h-28',
        'xs'  => 'w-40 h-28 md:w-48 md:h-32',
        'sm'  => 'w-52 h-36 md:w-60 md:h-40',
        'md'  => 'w-64 h-44 md:w-72 md:h-48',
    ];
    $bottomClasses = [
        'xxs' => 'bottom-2 md:bottom-3',
        'xs'  => 'bottom-3 md:bottom-4',
        'sm'  => 'bottom-4 md:bottom-8',
        'md'  => 'bottom-6 md:bottom-10',
    ];
    $peekClasses = [
        'xxs' => !false ? '-translate-y-1 md:translate-y-2 group-hover:-translate-y-6 md:group-hover:-translate-y-8 z-20'
                        : '-translate-y-12 md:-translate-y-16 hover:scale-105 z-30',
        'xs'  => '-translate-y-2 md:translate-y-4 group-hover:-translate-y-8 md:group-hover:-translate-y-12 z-20',
        'sm'  => '-translate-y-2 md:translate-y-4 group-hover:-translate-y-8 md:group-hover:-translate-y-12 z-20',
        'md'  => '-translate-y-2 md:translate-y-4 group-hover:-translate-y-8 md:group-hover:-translate-y-12 z-20',
    ];
    $openedPeekClasses = [
        'xxs' => '-translate-y-12 md:-translate-y-16 hover:scale-105 z-30',
        'xs'  => '-translate-y-16 md:-translate-y-20 hover:scale-105 z-30',
        'sm'  => '-translate-y-16 md:-translate-y-20 hover:scale-105 z-30',
        'md'  => '-translate-y-16 md:-translate-y-20 hover:scale-105 z-30',
    ];
    $cardClass = $cardSizeClasses[$size] ?? $cardSizeClasses['sm'];
    $bottomClass = $bottomClasses[$size] ?? $bottomClasses['sm'];
    $peekClass = $peekClasses[$size] ?? $peekClasses['sm'];
    $openedClass = $openedPeekClasses[$size] ?? $openedPeekClasses['sm'];
@endphp

<div
    x-data="{ isOpen: false }"
    x-init="$watch('isOpen', () => {})"
    :class="isOpen ? '{{ $openedClass }}' : '{{ $peekClass }}'"
    class="absolute left-1/2 -translate-x-1/2 bg-white border border-slate-200 rounded-xl shadow-2xl transition-all duration-300 ease-in-out flex items-center justify-center px-3 py-4 {{ $cardClass }} {{ $bottomClass }}"
>
    {{ $slot }}
</div>