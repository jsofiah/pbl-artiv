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

{{-- Modal Konfirmasi Approve --}}
<div id="approve-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     onclick="if(event.target === this) closeApproveModal()">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="px-6 py-5">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-full bg-[#D5FC55] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-neutral-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-lg text-slate-900">Setujui Hasil Desain?</h3>
                    <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                        Setelah disetujui, pesanan akan <strong>ditandai selesai</strong> dan Anda tidak bisa meminta revisi lagi.
                    </p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-50 rounded-b-2xl flex justify-end gap-2">
            <button type="button"
                    onclick="closeApproveModal()"
                    class="px-5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-100 transition">
                Batal
            </button>
            <button type="button"
                    onclick="submitApprove()"
                    class="px-5 py-2 rounded-xl bg-[#D5FC55] text-neutral-900 text-sm font-bold hover:bg-[#c5ec45] transition
                           shadow-[0_2px_8px_-2px_rgba(213,252,85,0.5)]">
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

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

<div id="modal-refund-proof"
     class="hidden fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4"
     onclick="if(event.target === this) closeRefundProof()">
    <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 shrink-0">
            <div>
                <p class="font-bold text-slate-900 text-sm">Bukti Transfer Refund</p>
                <p id="refund-proof-name" class="text-xs text-slate-400 truncate"></p>
            </div>
            <button type="button"
                    onclick="closeRefundProof()"
                    class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-slate-50">
            <div id="refund-proof-loading" class="text-sm text-slate-400">Memuat...</div>
            <img id="refund-proof-img" src="" alt="Bukti Transfer"
                 class="hidden max-w-full max-h-[70vh] rounded-lg shadow object-contain">
            <iframe id="refund-proof-pdf" src=""
                    class="hidden w-full h-[70vh] rounded-lg"></iframe>
        </div>
    </div>
</div>

<div id="modal-revision-payment"
     class="hidden fixed inset-0 z-[120] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
     onclick="if(event.target === this) closeRevisionPaymentModal()">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Beli Kuota Revisi</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Kuota revisi gratis ({{ \App\Models\Order::FREE_REVISION_LIMIT }}x) sudah habis.
                </p>
            </div>
            <button type="button" onclick="closeRevisionPaymentModal()"
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="revision-payment-loading" class="py-8 text-center text-sm text-slate-400">
            Memuat data...
        </div>

        <div id="revision-payment-error" class="hidden p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700 mb-4"></div>

        <div id="revision-payment-pending" class="hidden text-center py-6">
            <div class="w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="font-bold text-slate-900 mb-1">Menunggu Verifikasi</p>
            <p class="text-xs text-slate-500" id="revision-pending-at"></p>
            <p class="text-xs text-slate-400 mt-2">Admin akan memverifikasi pembayaran Anda segera.</p>
        </div>

        <div id="revision-payment-form" class="hidden">
            <div class="bg-slate-50 rounded-2xl p-4 mb-4 flex gap-4 items-center">
                <div class="w-32 h-32 shrink-0 bg-white rounded-xl p-2 shadow-sm border border-slate-200">
                    <img id="revision-qr-img"
                         src=""
                         alt="QRIS"
                         class="w-full h-full object-contain">
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Bayar</p>
                    <p id="revision-amount-text" class="text-xl font-extrabold text-[#6D28D9] mt-0.5"></p>
                    <p class="text-xs text-slate-500 mt-1">Merchant: <span class="font-semibold">ARTIV Official</span></p>
                </div>
            </div>

            <form id="revision-payment-form-element"
                  method="POST"
                  action="{{ route('customer.pesanan.revision.payment', $order->id) }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Bukti Pembayaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="file"
                           name="proof"
                           id="revision-proof-input"
                           required
                           accept="image/png,image/jpeg,image/webp"
                           onchange="showRevisionProofPreview(this)"
                           class="w-full text-sm border border-slate-200 rounded-xl file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-[#6D28D9] hover:file:bg-violet-100">
                    <p class="text-xs text-slate-400 mt-1.5">Format: PNG, JPG, WEBP · Maks. 5 MB</p>

                    <div id="revision-proof-preview" class="hidden mt-3 p-2 bg-slate-50 rounded-lg border border-slate-200">
                        <p class="text-xs text-slate-600 truncate" id="revision-proof-name"></p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button"
                            onclick="closeRevisionPaymentModal()"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-[#6D28D9] hover:bg-[#5B21B6] rounded-xl shadow-sm">
                        Kirim Bukti
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>