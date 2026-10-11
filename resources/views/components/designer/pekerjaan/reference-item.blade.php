@props(['ref', 'order'])

@php
    $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
    $isImage = $ref->type === 'file' && in_array(strtolower($ref->mime_type ?? ''), $imageMimes);
@endphp

<div class="flex items-center justify-between bg-slate-50 rounded-lg p-2">
    <div class="min-w-0 flex-1">
        <p class="text-xs font-semibold text-slate-800 truncate">
            {{ $ref->file_name ?? $ref->external_url }}
        </p>
    </div>
    @if ($ref->type === 'file' && $ref->file_url)
        <div class="flex items-center gap-1 shrink-0 ml-2">
            @if ($isImage)
                <button type="button"
                        onclick="previewImage(
                            '{{ route('designer.pekerjaan.reference.preview', ['order' => $order->id, 'reference' => $ref->id]) }}',
                            '{{ route('designer.pekerjaan.reference.download', ['order' => $order->id, 'reference' => $ref->id]) }}',
                            '{{ addslashes($ref->file_name ?? 'Gambar') }}'
                        )"
                        class="text-slate-400 hover:text-[#6D28D9] transition"
                        title="Lihat gambar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            @endif

            <a href="{{ route('designer.pekerjaan.reference.download', ['order' => $order->id, 'reference' => $ref->id]) }}"
               class="text-slate-400 hover:text-[#6D28D9] transition"
               title="Unduh file">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
            </a>
        </div>
    @endif
</div>