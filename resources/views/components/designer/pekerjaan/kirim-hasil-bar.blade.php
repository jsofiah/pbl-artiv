@props([
    'canSend' => false,
    'status'  => null,
])

<div id="kirim-hasil-wrapper"
     class="border-t border-slate-100 px-4 py-3 bg-violet-50/50">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div id="kirim-hasil-info"
             class="flex items-center gap-2 text-xs {{ $canSend ? 'text-violet-700' : 'text-slate-400' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span id="kirim-hasil-text">
                @if ($status === 'revision_needed')
                    Customer minta revisi. Kirim hasil perbaikan.
                @elseif ($status === 'in_progress')
                    Sudah selesai mengerjakan?
                @elseif ($status === 'deliverable_sent')
                    Menunggu review customer.
                @else
                    Belum waktunya kirim hasil.
                @endif
            </span>
        </div>

        <button type="button"
                id="kirim-hasil-btn"
                data-can-send="{{ $canSend ? '1' : '0' }}"
                @if ($canSend)
                    onclick="document.getElementById('deliverable-modal').classList.remove('hidden')"
                @else
                    disabled
                @endif
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition
                    {{ $canSend
                            ? 'bg-[#6D28D9] text-white hover:bg-[#5B21B6] cursor-pointer'
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Kirim Hasil Desain
        </button>
    </div>
</div>