<div id="deliverable-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     onclick="if(event.target === this) this.classList.add('hidden')">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <form method="POST"
              action="{{ route('designer.pekerjaan.kirimDeliverable', $order->id) }}"
              enctype="multipart/form-data">
            @csrf

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900">Kirim Hasil Desain</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        File akan dikirim ke customer untuk direview.
                    </p>
                </div>
                <button type="button"
                        onclick="document.getElementById('deliverable-modal').classList.add('hidden')"
                        class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        File Hasil <span class="text-red-500">*</span>
                        <span class="text-slate-400 font-normal">(bisa lebih dari 1)</span>
                    </label>

                    <input type="file"
                           name="attachments[]"
                           id="deliverable-files"
                           required
                           multiple
                           accept=".png,.jpg,.jpeg,.gif,.webp,.pdf,.zip,"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm
                                  file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                  file:text-xs file:font-semibold file:bg-violet-50 file:text-[#6D28D9]
                                  hover:file:bg-violet-100"
                           onchange="updateDeliverablePreview(this)">

                    <p class="text-xs text-slate-400 mt-1.5">
                        Maks. 25 MB per file. Format: PNG, JPG, PDF, ZIP
                    </p>

                    <div id="deliverable-files-preview" class="hidden mt-3 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">File terpilih:</span>
                            <span id="deliverable-files-count" class="font-semibold text-slate-700">0 file</span>
                        </div>
                        <div id="deliverable-files-list" class="space-y-1.5 max-h-40 overflow-y-auto"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Catatan untuk Customer <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <textarea name="isi"
                              rows="3"
                              maxlength="2000"
                              placeholder="Contoh: Ini versi final poster, silakan dicek..."
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700
                                     placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30
                                     focus:border-[#6D28D9] resize-none"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button"
                        onclick="document.getElementById('deliverable-modal').classList.add('hidden')"
                        class="px-5 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 rounded-xl bg-[#6D28D9] text-white text-sm font-semibold hover:bg-[#5B21B6] transition">
                    Kirim ke Customer
                </button>
            </div>
        </form>
    </div>
</div>