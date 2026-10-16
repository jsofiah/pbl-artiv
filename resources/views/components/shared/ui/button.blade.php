@props([
    'variant' => 'primary',
    'size'    => 'md',
    'type'    => 'button',
    'href'    => null,
    'icon'    => null,
    'iconPos' => 'left',
    'block'   => false,
])

@php
    $variants = [
        'primary'   => 'bg-[#6D28D9] text-white hover:bg-[#5B21B6]',
        'lime'      => 'bg-[#D5FC55] text-neutral-900 hover:bg-[#c5ec45] font-bold',
        'lime-soft' => 'bg-lime-300 text-lime-900 hover:bg-lime-400 font-bold',
        'success'   => 'bg-emerald-500 text-white hover:bg-emerald-600',
        'danger'    => 'bg-red-500 text-white hover:bg-red-600',
        'warning'   => 'bg-orange-500 text-white hover:bg-orange-600',
        'outline'   => 'bg-white border border-slate-200 text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9]',
        'outline-warning' => 'bg-white border border-orange-300 text-orange-600 hover:bg-orange-50',
        'ghost'     => 'bg-transparent text-slate-500 hover:bg-slate-100 hover:text-[#6D28D9]',
        'ghost-dark' => 'bg-white/20 text-white hover:bg-white/30',
        'violet-soft' => 'bg-violet-50 text-[#6D28D9] hover:bg-violet-100',
    ];

    $sizes = [
        'sm' => 'text-xs px-3 py-1.5 gap-1',
        'md' => 'text-sm px-4 py-2 gap-1.5',
        'lg' => 'text-sm px-5 py-2.5 gap-2',
        'icon' => 'w-9 h-9 p-0',
        'icon-lg' => 'w-10 h-10 p-0',
    ];

    $base = 'inline-flex items-center justify-center rounded-xl font-semibold transition shrink-0';
    $classes = $base
        . ' ' . ($variants[$variant] ?? $variants['primary'])
        . ' ' . ($sizes[$size] ?? $sizes['md'])
        . ($block ? ' w-full' : '');

    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if ($icon && $iconPos === 'left')
        <span class="shrink-0">{!! $icon !!}</span>
    @endif

    {{ $slot }}

    @if ($icon && $iconPos === 'right')
        <span class="shrink-0">{!! $icon !!}</span>
    @endif
</{{ $tag }}>