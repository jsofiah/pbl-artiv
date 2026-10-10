@props([
    'title'    => null,
    'subtitle' => null,
    'action'   => null,
    'padding'  => 'p-5',
    'shadow'   => true,
])

<div {{ $attributes->merge([
    'class' => 'bg-white rounded-2xl border border-slate-100 ' . $padding . ($shadow ? ' shadow-sm' : '')
]) }}>
    @if ($title || $action || $subtitle)
        <div class="flex items-start justify-between gap-3 {{ ($title || $subtitle) ? 'mb-4' : '' }}">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-xs font-bold tracking-wide text-slate-400">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>

            @if ($action)
                <div class="shrink-0">{{ $action }}</div>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>