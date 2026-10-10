@props([
    'user'  => null,
    'src'   => null,
    'name'  => null,
    'size'  => 'md',
    'ring'  => false,
    'color' => 'violet',
])

@php
    use App\Helpers\R2Helper;

    $sizes = [
        'xs' => 'w-7 h-7 text-xs',
        'sm' => 'w-9 h-9 text-sm',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-11 h-11 text-base',
        'xl' => 'w-14 h-14 text-lg',
    ];

    $colors = [
        'violet' => 'bg-violet-100 text-[#6D28D9]',
        'lime'   => 'bg-lime-100 text-lime-700',
        'slate'  => 'bg-slate-100 text-slate-600',
        'blue'   => 'bg-blue-100 text-blue-700',
        'amber'  => 'bg-amber-100 text-amber-700',
    ];

    $sizeCls  = $sizes[$size]  ?? $sizes['md'];
    $colorCls = $colors[$color] ?? $colors['violet'];

    $resolvedSrc = $src;
    if (!$resolvedSrc && $user && $user->avatar_url) {
        $resolvedSrc = R2Helper::url($user->avatar_url);
    }

    $resolvedName = $name ?? $user?->full_name ?? '?';
    $initial = strtoupper(substr($resolvedName, 0, 1));

    $ringCls = $ring ? 'ring-2 ring-white' : '';
@endphp

<div {{ $attributes->merge([
    'class' => "rounded-full flex items-center justify-center font-bold overflow-hidden shrink-0 $sizeCls $colorCls $ringCls"
]) }}>
    @if ($resolvedSrc)
        <img src="{{ $resolvedSrc }}" alt="{{ $resolvedName }}" class="w-full h-full object-cover">
    @else
        {{ $initial }}
    @endif
</div>