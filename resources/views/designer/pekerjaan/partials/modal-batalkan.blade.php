<div id="modal-batalkan"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
     onclick="if(event.target === this) document.getElementById('modal-batalkan').classList.add('hidden')">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 shrink-0">
            <p class="font-bold text-slate-900 text-sm truncate">Ajukan Pembatalan</p>
            <button type="button"
                    onclick="document.getElementById('modal-batalkan').classList.add('hidden')"
                    class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('designer.pekerjaan.batalkan', $order->id) }}" class="flex-1 min-h-0 overflow-auto">
            @csrf
            <div class="px-5 py-4">
                <p class="text-sm text-slate-600 mb-4">
                    Berikan alasan mengapa kamu tidak dapat melanjutkan pekerjaan ini.
                    Customer akan memilih: refund atau dilanjutkan designer pengganti.
                </p>

                <textarea name="alasan" required minlength="10" maxlength="1000" rows="4"
                          placeholder="Contoh: Saya sedang sakit..."
                          class="w-full text-sm border border-slate-200 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9] transition"></textarea>
            </div>

            <div class="px-5 py-3 border-t border-slate-100 flex justify-end items-center gap-2 shrink-0">
                <button type="button"
                        onclick="document.getElementById('modal-batalkan').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                    Kembali
                </button>
                <button type="submit"
                        data-confirm="Yakin ingin membatalkan order ini?"
                        class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                    Batalkan
                </button>
            </div>
        </form>
    </div>
</div>