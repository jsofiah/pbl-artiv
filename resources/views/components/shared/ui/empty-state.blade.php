@props([
    'title'       => 'Tidak ada data',
    'description' => null,
    'icon'        => null,
    'action'      => null,
])

@php
    $iconPaths = [
        'inbox'     => 'M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z',
        'search'    => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
        'chat'      => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z',
        'file'      => 'M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    ];

    $iconPath = $icon ? ($iconPaths[$icon] ?? null) : null;
@endphp

<div {{ $attributes->merge([
    'class' => 'rounded-2xl border-2 border-dashed border-violet-200 bg-white/50 px-6 py-12 text-center'
]) }}>
    @if ($iconPath)
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-violet-100 bg-violet-50">
            <svg class="h-8 w-8 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                @foreach (explode('|', $iconPath) as $path)
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                @endforeach
            </svg>
        </div>
    @endif

    <h3 class="text-lg font-bold text-slate-900">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">{{ $description }}</p>
    @endif

    @if ($action)
        <div class="mt-6">{{ $action }}</div>
    @endif
</div>