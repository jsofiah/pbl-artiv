@props([
    'variant' => 'violet',
    'size'    => 'md',
    'dot'     => false,
    'pill'    => true,
])

@php
    $variants = [
        'violet'  => 'bg-violet-50 text-[#6D28D9] border-violet-200',
        'lime'    => 'bg-lime-100 text-lime-700 border-lime-200',
        'amber'   => 'bg-amber-50 text-amber-600 border-amber-200',
        'blue'    => 'bg-blue-50 text-blue-600 border-blue-200',
        'orange'  => 'bg-orange-50 text-orange-600 border-orange-200',
        'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
        'red'     => 'bg-red-50 text-red-600 border-red-200',
        'slate'   => 'bg-slate-100 text-slate-600 border-slate-200',
        // Solid varian (untuk deliverable badge)
        'solid-violet'  => 'bg-violet-600 text-white border-transparent',
        'solid-lime'    => 'bg-lime-300 text-lime-900 border-transparent',
        'solid-emerald' => 'bg-emerald-500 text-white border-transparent',
        'solid-amber'   => 'bg-amber-400 text-amber-900 border-transparent',
    ];

    $sizes = [
        'sm' => 'text-[10px] px-2 py-0.5',
        'md' => 'text-xs px-2.5 py-0.5',
        'lg' => 'text-sm px-3 py-1',
    ];

    $variantCls = $variants[$variant] ?? $variants['violet'];
    $sizeCls    = $sizes[$size]      ?? $sizes['md'];
    $shapeCls   = $pill ? 'rounded-full' : 'rounded-md';

    $dotSizes = ['sm' => 'w-1 h-1', 'md' => 'w-1.5 h-1.5', 'lg' => 'w-2 h-2'];
    $dotCls   = $dotSizes[$size] ?? $dotSizes['md'];
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center gap-1.5 border font-semibold $shapeCls $sizeCls $variantCls"
]) }}>
    @if ($dot)
        <span class="rounded-full bg-current {{ $dotCls }}"></span>
    @endif

    {{ $slot }}
</span>