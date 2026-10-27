<x-shared.modals.base
    id="edit-keyword-modal"
    title="Edit Blacklist Keyword"
    subtitle="Perbarui kata kunci dan tipe klasifikasi"
    size="sm"
    showVar="showEditModal">

    <form :action="editActionUrl" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kata Kunci / Frasa <span class="text-red-500">*</span></label>
            <input type="text" name="keyword" x-model="editKeyword" required
                   class="w-full text-sm py-2.5 px-3.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipe Keyword <span class="text-red-500">*</span></label>
            <select name="type" x-model="editType" required
                    class="w-full text-sm py-2.5 px-3.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
                <option value="word">Word (Kata Dilarang / Frasa)</option>
                <option value="link">Link (Tautan URL / Domain Luar)</option>
                <option value="phone">Phone (Nomor Telepon / WhatsApp)</option>
                <option value="email">Email (Alamat Email)</option>
            </select>
        </div>

        <div class="pt-2">
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" x-model="editIsActive"
                    class="rounded border-gray-300 text-[#6D28D9] focus:ring-[#6D28D9]">
                <span class="text-sm text-gray-700 font-medium">Status Aktif</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-2">
            <button type="button" @click="showEditModal = false"
                    class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                Batal
            </button>
            <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-[#6D28D9] hover:bg-[#5B21B6] rounded-xl transition shadow-sm">
                Perbarui Keyword
            </button>
        </div>
    </form>
</x-shared.modals.base>
