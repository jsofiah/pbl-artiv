<x-shared.modals.base
    id="add-keyword-modal"
    title="Tambah Blacklist Keyword"
    subtitle="Cegah pertukaran kontak / transaksi di luar sistem"
    size="sm"
    showVar="showAddModal">

    <form action="{{ route('admin.pengaturan.blacklist.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kata Kunci / Frasa <span class="text-red-500">*</span></label>
            <input type="text" name="keyword" x-model="addKeyword" required
                   placeholder="Contoh: wa.me, 0812, transfer langsung"
                   class="w-full text-sm py-2.5 px-3.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipe Keyword <span class="text-red-500">*</span></label>
            <select name="type" x-model="addType" required
                    class="w-full text-sm py-2.5 px-3.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
                <option value="word">Word (Kata Dilarang / Frasa)</option>
                <option value="link">Link (Tautan URL / Domain Luar)</option>
                <option value="phone">Phone (Nomor Telepon / WhatsApp)</option>
                <option value="email">Email (Alamat Email)</option>
            </select>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-2">
            <button type="button" @click="showAddModal = false"
                    class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                Batal
            </button>
            <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-[#6D28D9] hover:bg-[#5B21B6] rounded-xl transition shadow-sm">
                Simpan Keyword
            </button>
        </div>
    </form>
</x-shared.modals.base>
