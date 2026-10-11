<form method="POST"
      action="{{ route('designer.pekerjaan.kirimPesan', $order->id) }}"
      enctype="multipart/form-data"
      class="border-t border-slate-100 px-4 py-3">
    @csrf

    <div id="file-preview" class="hidden mb-2 px-2">
        <div class="inline-flex items-center gap-2 bg-slate-100 rounded-full pl-3 pr-2 py-1.5 text-sm">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5l4-4a3 3 0 10-4.24-4.24l-6 6a3 3 0 000 4.24m3 3.5l-4 4a3 3 0 11-4.24-4.24l6-6a3 3 0 014.24 0"/>
            </svg>
            <span id="file-name" class="text-slate-700 max-w-[200px] truncate"></span>
            <button type="button" onclick="clearFileInput()"
                    class="w-6 h-6 rounded-full hover:bg-slate-200 flex items-center justify-center text-slate-500">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="flex items-center gap-2 relative">
        {{-- Tombol Lampirkan --}}
        <button type="button" id="lampiran-btn"
                @if ($canUploadFile)
                    onclick="document.getElementById('attachment-input').click()"
                @else
                    disabled
                @endif
                class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition
                    {{ $canUploadFile
                            ? 'hover:bg-slate-100 text-slate-500 hover:text-[#6D28D9] cursor-pointer'
                            : 'text-slate-300 cursor-not-allowed' }}"
                title="{{ $canUploadFile ? 'Lampirkan file' : ($isChatOpen ? 'Tidak bisa upload file sekarang' : 'Chat ditutup') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
            </svg>
        </button>

        <input type="file" id="attachment-input" name="attachment" class="hidden"
               accept="*/*" onchange="showFilePreview(this)"
               @if (!$canUploadFile) disabled @endif>

        {{-- Input text --}}
        <input type="text" name="isi" id="chat-input"
               value="{{ old('isi') }}"
               placeholder="{{ $isChatOpen ? 'Tulis pesan untuk customer...' : 'Percakapan telah ditutup' }}"
               class="flex-1 rounded-full px-4 py-2.5 text-sm
                    {{ $isChatOpen
                            ? 'bg-slate-50 text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30'
                            : 'bg-slate-100 text-slate-400 placeholder:text-slate-400 cursor-not-allowed' }}"
               @if (!$isChatOpen) disabled @endif>

        {{-- Tombol Emoji --}}
        <button type="button" id="emoji-toggle"
                @if ($isChatOpen) onclick="" @else disabled @endif
                class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition
                    {{ $isChatOpen
                            ? 'hover:bg-slate-100 text-slate-500 hover:text-[#6D28D9] cursor-pointer'
                            : 'text-slate-300 cursor-not-allowed' }}"
                title="{{ $isChatOpen ? 'Emoji' : 'Chat ditutup' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
            </svg>
        </button>

        {{-- Tombol Kirim --}}
        <button type="submit" id="kirim-btn"
                @if (!$isChatOpen) disabled @endif
                class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 rounded-full text-sm font-semibold transition
                    {{ $isChatOpen
                            ? 'bg-[#6D28D9] hover:bg-[#5B21B6] text-white cursor-pointer'
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
            <span class="hidden sm:inline">Kirim</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z" />
            </svg>
        </button>

        @include('designer.pekerjaan.partials.emoji-picker')
    </div>

    <p class="text-xs text-slate-400 mt-2 ml-1">
        @if ($isChatOpen)
            Format didukung: PNG, JPG, ZIP, PDF (Maks. 25 MB)
        @else
            Chat hanya bisa dibaca. Tidak bisa kirim pesan lagi.
        @endif
    </p>
</form>