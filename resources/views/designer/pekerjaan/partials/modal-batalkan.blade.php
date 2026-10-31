<div id="modal-batalkan" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="text-lg font-bold mb-2">Ajukan Pembatalan</h3>
        <p class="text-sm text-gray-600 mb-4">
            Berikan alasan mengapa kamu tidak dapat melanjutkan pekerjaan ini.
            Customer akan memilih: refund atau dilanjutkan designer pengganti.
        </p>

        <form method="POST" action="{{ route('designer.pekerjaan.batalkan', $order->id) }}">
            @csrf
            <textarea name="alasan" required minlength="10" maxlength="1000" rows="4"
                      placeholder="Contoh: Saya sedang sakit..."
                      class="w-full text-sm border border-gray-200 rounded-lg p-3 mb-4"></textarea>

            <div class="flex gap-2">
                <button type="button"
                        onclick="document.getElementById('modal-batalkan').classList.add('hidden')"
                        class="flex-1 px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Kembali
                </button>
                <button type="submit"
                        data-confirm="Yakin ingin membatalkan order ini?"
                        class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg">
                    Batalkan
                </button>
            </div>
        </form>
    </div>
</div>