<x-shared.modals.base
    id="delete-keyword-modal"
    size="sm"
    showVar="showDeleteModal">

    <div class="text-center">
        <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>

        <h3 class="text-base font-bold text-gray-900 mb-1">Hapus Keyword Blacklist?</h3>
        <p class="text-sm text-gray-500 mb-5 leading-relaxed">
            Anda yakin ingin menghapus keyword
            <span class="font-bold text-gray-800" x-text="'&ldquo;' + deleteKeywordName + '&rdquo;'"></span>?
        </p>

        <form :action="deleteActionUrl" method="POST" class="flex items-center justify-center gap-3">
            @csrf
            @method('DELETE')
            <button type="button" @click="showDeleteModal = false"
                    class="w-1/2 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                Batal
            </button>
            <button type="submit"
                    class="w-1/2 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition shadow-sm">
                Ya, Hapus
            </button>
        </form>
    </div>
</x-shared.modals.base>
