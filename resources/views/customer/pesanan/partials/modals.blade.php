{{-- Brief Modal --}}
@if ($order->brief_note)
    <x-shared.modals.base
        id="brief-modal"
        title="Brief Pesanan Klien"
        :subtitle="$order->product->name . ' · ' . $order->order_code"
        size="lg">

        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
            {{ $order->brief_note }}
        </p>

        <x-slot:footer>
            <x-shared.ui.button
                variant="primary"
                onclick="document.getElementById('brief-modal').classList.add('hidden')">
                Tutup
            </x-shared.ui.button>
        </x-slot:footer>
    </x-shared.modals.base>
@endif

{{-- Revision Modal --}}
<x-shared.modals.base
    id="revision-modal"
    title="Minta Revisi"
    subtitle="Jelaskan bagian mana yang perlu diperbaiki."
    closeFn="closeRevisionModal">

    <form method="POST" id="revision-form" action="">
        @csrf

        <label class="block text-sm font-semibold text-slate-700 mb-2">
            Catatan Revisi <span class="text-red-500">*</span>
        </label>

        <textarea name="revision_note"
                  id="revision-note"
                  rows="5"
                  maxlength="2000"
                  required
                  placeholder="Contoh: Tolong ganti warna background jadi biru, dan logo diperbesar sedikit..."
                  class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700
                         placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30
                         focus:border-[#6D28D9] resize-none"></textarea>

        <p class="text-xs text-slate-400 mt-1.5">
            Minimal 5 karakter. Sisa: <span id="revision-char-count">2000</span>
        </p>
    </form>

    <x-slot:footer>
        <x-shared.ui.button variant="ghost" onclick="closeRevisionModal()">
            Batal
        </x-shared.ui.button>
        <x-shared.ui.button variant="warning" type="submit" form="revision-form">
            Kirim Revisi
        </x-shared.ui.button>
    </x-slot:footer>
</x-shared.modals.base>

<div id="image-preview-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
     onclick="if(event.target === this) closeImagePreview()">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">

        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 shrink-0">
            <p id="image-preview-title" class="font-bold text-slate-900 text-sm truncate">
                Preview
            </p>
            <button type="button" onclick="closeImagePreview()"
                    class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 min-h-0 bg-slate-100 flex items-center justify-center p-4 overflow-auto">
            <img id="image-preview-img"
                 src=""
                 alt="Preview"
                 class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
        </div>

        <div class="px-5 py-3 border-t border-slate-100 flex justify-between items-center shrink-0">
            <p class="text-xs text-slate-400">
                Tekan ESC atau klik luar untuk menutup
            </p>
            <a id="image-preview-download"
               href="#"
               class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-[#6D28D9] text-white hover:bg-[#5B21B6] transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
                Unduh
            </a>
        </div>

    </div>
</div>