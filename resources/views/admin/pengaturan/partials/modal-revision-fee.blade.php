<x-shared.modals.base
    id="fee-modal"
    title="Ubah Harga Revisi Tambahan"
    subtitle="Atur besaran biaya per pengajuan revisi berbayar"
    size="sm"
    showVar="showFeeModal">

    <form action="{{ route('admin.pengaturan.revision-fee.update') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Layanan / Keterangan</label>
            <input type="text" name="name" x-model="feeName" placeholder="Revisi Tambahan"
                   class="w-full text-sm py-2.5 px-3.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nominal Tarif (Rupiah) <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute left-3.5 top-2.5 text-sm font-bold text-gray-400">Rp</span>
                <input type="number" name="fee" x-model="feeAmount" min="0" step="500" required
                       class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition font-bold text-gray-800">
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Biaya yang harus ditransfer customer untuk revisi ke-4 dan seterusnya.</p>
        </div>

        <div class="pt-2">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $revisionFee->is_active ? 'checked' : '' }}
                       class="rounded border-gray-300 text-[#6D28D9] focus:ring-[#6D28D9]">
                <span class="text-sm text-gray-700 font-medium">Status Tarif Aktif</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-2">
            <button type="button" @click="showFeeModal = false"
                    class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                Batal
            </button>
            <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-[#6D28D9] hover:bg-[#5B21B6] rounded-xl transition shadow-sm">
                Simpan Tarif
            </button>
        </div>
    </form>
</x-shared.modals.base>
