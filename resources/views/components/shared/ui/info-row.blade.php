@props([
    'label'    => null,
    'value'    => null,
    'sub'      => null,
    'icon'     => null,
    'iconColor' => 'text-slate-400',
    'layout'   => 'row',
    'size'     => 'md',
])

@php
    $iconPaths = [
        'clock'    => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
        'star'     => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
        'user'     => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'tag'      => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z|M6 6h.008v.008H6V6z',
        'check'    => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ];

    $iconPath = $icon ? ($iconPaths[$icon] ?? null) : null;
    $iconSizeCls = $size === 'sm' ? 'w-4 h-4' : 'w-5 h-5';

    if ($layout === 'row') {
        $wrapCls = 'flex items-center justify-between gap-3';
        $labelCls = 'text-sm text-slate-500 ' . ($size === 'sm' ? 'text-xs' : '');
        $valueCls = 'font-semibold text-slate-900 ' . ($size === 'sm' ? 'text-xs' : 'text-sm');
    } else {
        $wrapCls = 'block';
        $labelCls = 'text-xs text-slate-500 mb-1';
        $valueCls = 'font-bold text-slate-900 ' . ($size === 'sm' ? 'text-xs' : 'text-sm');
    }
@endphp

<div {{ $attributes->merge(['class' => $wrapCls]) }}>
    <div class="flex items-center gap-1.5 min-w-0">
        @if ($iconPath)
            <svg class="{{ $iconSizeCls }} {{ $iconColor }} shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                @foreach (explode('|', $iconPath) as $path)
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                @endforeach
            </svg>
        @endif
        <span class="{{ $labelCls }}">{{ $label }}</span>
    </div>

    <div class="min-w-0">
        <p class="{{ $valueCls }}">{{ $value }}</p>
        @if ($sub)
            <p class="text-xs text-slate-400">{{ $sub }}</p>
        @endif
    </div>
</div>