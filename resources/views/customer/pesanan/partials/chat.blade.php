@php
    $designer = $order->designer;
    $conv     = $order->conversation;
@endphp

<div class="lg:sticky lg:top-20 h-[calc(100vh-160px)]">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-full overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <x-shared.ui.avatar :user="$designer" size="md" />

                <div>
                    <p class="font-bold text-slate-900 leading-tight">
                        {{ $designer->full_name ?? 'Belum ada desainer' }}
                    </p>
                    <p class="text-xs text-emerald-600 font-medium">
                        {{ $designer ? 'Online' : '-' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto px-6 py-5 space-y-4" id="chat-container">
            @php $lastDate = null; @endphp

            @forelse ($conv?->messages ?? [] as $msg)
                @php
                    $currentDate = $msg->created_at->toDateString();
                    $showDivider = $lastDate !== $currentDate;
                    $lastDate = $currentDate;
                    $isMe = $msg->sender_id === auth()->id();
                @endphp

                @if ($showDivider)
                    <x-shared.chat.date-divider :date="$msg->created_at" />
                @endif

                <x-shared.chat.bubble :msg="$msg" :order="$order" :isMe="$isMe" />
            @empty
                <div class="flex items-center justify-center h-full">
                    <p class="text-sm text-slate-400">Belum ada pesan.</p>
                </div>
            @endforelse
        </div>

        <div class="px-4">
            @if ($errors->any())
                <div id="error-notif" class="mt-3 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-red-700">Pesan tidak dapat dikirim</p>
                        <ul class="text-xs text-red-600 mt-1 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button"
                            onclick="document.getElementById('error-notif').remove()"
                            class="text-red-400 hover:text-red-600 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        @php
            $isChatOpen   = $order->isChatOpen();
            $chatClosesAt = $order->chatClosesAt();
        @endphp

        @if ($isChatOpen)
            @if ($order->status === 'completed' && $chatClosesAt)
                <div class="px-4 pt-3">
                    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-amber-800">Pesanan telah selesai 🎉</p>
                            <p class="text-xs text-amber-700 mt-0.5">
                                Percakapan masih terbuka sampai
                                <strong>{{ $chatClosesAt->translatedFormat('d M Y, H:i') }} WIB</strong>
                                ({{ $chatClosesAt->diffForHumans(now()) }}).
                                Setelah itu chat hanya bisa dibaca.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('customer.pesanan.kirimPesan', $order->id) }}"
                  enctype="multipart/form-data"
                  class="border-t border-slate-100 px-4 py-3 relative">
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

                <div class="flex items-center gap-2">
                    <button type="button"
                            onclick="document.getElementById('attachment-input').click()"
                            class="shrink-0 w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-[#6D28D9] transition"
                            title="Lampirkan file">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                        </svg>
                    </button>

                    <input type="file"
                           id="attachment-input"
                           name="attachment"
                           class="hidden"
                           accept="*/*"
                           onchange="showFilePreview(this)">

                    <input type="text"
                           name="isi"
                           id="chat-input"
                           value="{{ old('isi') }}"
                           placeholder="Tulis pesan atau diskusi dengan desainer..."
                           class="flex-1 bg-slate-50 rounded-full px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30"
                           required>

                    <button type="button"
                            onclick="document.getElementById('emoji-picker').classList.toggle('hidden')"
                            class="shrink-0 w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-[#6D28D9] transition"
                            title="Emoji">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                        </svg>
                    </button>

                    <button type="submit"
                            class="shrink-0 inline-flex items-center gap-1.5 bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-sm font-semibold px-4 py-2.5 rounded-full transition"
                            title="Kirim">
                        <span class="hidden sm:inline">Kirim</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z" />
                        </svg>
                    </button>

                    <div id="emoji-picker"
                         class="hidden absolute bottom-20 right-6 bg-white rounded-2xl shadow-xl border border-slate-200 p-3 z-40 w-[280px]">
                        <div class="grid grid-cols-8 gap-1 max-h-48 overflow-y-auto">
                            @foreach (['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕','🤑','🤠','😈','👿','👹','👺','🤡','💩','👻','💀','👽','👾','🤖','🎃'] as $emoji)
                                <button type="button"
                                        onclick="insertEmoji('{{ $emoji }}')"
                                        class="w-8 h-8 rounded-lg hover:bg-slate-100 text-lg flex items-center justify-center transition">
                                    {{ $emoji }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-400 mt-2 ml-1">
                    Format didukung: PNG, JPG, ZIP, PDF (Maks. 25 MB)
                </p>
            </form>
        @else
            <div class="border-t border-slate-100 px-4 py-5 bg-slate-50">
                <div class="flex items-start gap-3 text-sm text-slate-500">
                    <svg class="w-5 h-5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold text-slate-700">Percakapan telah ditutup</p>
                        <p class="text-xs mt-1">
                            @if ($order->status === 'completed')
                                Pesanan sudah selesai dan masa tenggang percakapan telah berakhir.
                            @elseif ($order->status === 'cancelled')
                                Pesanan telah dibatalkan.
                            @else
                                Pesanan telah di-refund.
                            @endif
                            Anda masih bisa membaca riwayat chat di atas.
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>