@props([
    'att',
    'order',
    'theme' => 'light',
    'routePrefix' => null,
])

@php
    $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
    $isImage = in_array(strtolower($att->mime_type ?? ''), $imageMimes);

    $isDark = $theme === 'dark';

    $containerCls = $isDark
        ? 'bg-white/10 border-white/20'
        : 'bg-white border-slate-200';
    $thumbBgCls = $isDark ? 'bg-white/20' : 'bg-slate-100';
    $iconCls    = $isDark ? 'text-white' : 'text-slate-500';
    $fileNameCls = $isDark ? 'text-white' : 'text-slate-800';
    $fileSizeCls = $isDark ? 'text-violet-100' : 'text-slate-400';
    $previewBtnCls = $isDark
        ? 'bg-white/20 text-white hover:bg-white/30'
        : 'bg-violet-50 text-[#6D28D9] hover:bg-violet-100';

    // Auto-detect route prefix
    if (!$routePrefix) {
        $routePrefix = request()->routeIs('designer.*') ? 'designer' : 'customer';
    }

    $previewRoute  = "{$routePrefix}.pesanan.attachment.preview";
    $downloadRoute = "{$routePrefix}.pesanan.attachment.download";

    $previewUrl  = route($previewRoute,  ['order' => $order->id, 'attachment' => $att->id]);
    $downloadUrl = route($downloadRoute, ['order' => $order->id, 'attachment' => $att->id]);
    $safeName    = addslashes($att->file_name ?? 'Gambar');
@endphp

<div class="mt-3 {{ $containerCls }} border rounded-xl p-3 flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
        @if ($isImage)
            <button type="button"
                    onclick="previewImage('{{ $previewUrl }}', '{{ $downloadUrl }}', '{{ $safeName }}')"
                    class="w-12 h-12 rounded-lg {{ $thumbBgCls }} shrink-0 flex items-center justify-center group"
                    title="Lihat gambar">
                <svg class="w-5 h-5 {{ $iconCls }} group-hover:scale-110 transition"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
        @else
            <div class="w-12 h-12 rounded-lg {{ $thumbBgCls }} shrink-0 flex items-center justify-center">
                <svg class="w-5 h-5 {{ $isDark ? 'text-white/70' : 'text-slate-400' }}"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-6 4h6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5L17 7.5V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        @endif

        <div class="min-w-0">
            <p class="text-sm font-semibold {{ $fileNameCls }} truncate">{{ $att->file_name }}</p>
            <p class="text-xs {{ $fileSizeCls }}">
                {{ number_format(($att->file_size ?? 0) / 1024 / 1024, 1) }} MB
            </p>
        </div>
    </div>

    <div class="flex items-center gap-1 shrink-0">
        @if ($isImage)
            <button type="button"
                    onclick="previewImage('{{ $previewUrl }}', '{{ $downloadUrl }}', '{{ $safeName }}')"
                    class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-lg {{ $previewBtnCls }} transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Lihat
            </button>
        @endif

        <a href="{{ $downloadUrl }}"
           class="shrink-0 inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg bg-lime-300 text-lime-900 hover:bg-lime-400 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
            </svg>
            Unduh
        </a>
    </div>
</div>