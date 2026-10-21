<div id="image-preview-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
     onclick="if(event.target === this) closeImagePreview()">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 shrink-0">
            <p id="image-preview-title" class="font-bold text-slate-900 text-sm truncate">Preview</p>
            <button type="button" onclick="closeImagePreview()"
                    class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 min-h-0 bg-slate-100 flex items-center justify-center p-4 overflow-auto">
            <img id="image-preview-img" src="" alt="Preview"
                 class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
        </div>

        <div class="px-5 py-3 border-t border-slate-100 flex justify-between items-center shrink-0">
            <p class="text-xs text-slate-400">Tekan ESC atau klik luar untuk menutup</p>
            <a id="image-preview-download" href="#"
               class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-[#6D28D9] text-white hover:bg-[#5B21B6] transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
                Unduh
            </a>
        </div>
    </div>
</div>