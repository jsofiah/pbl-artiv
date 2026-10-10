@php
    $sections = [
        'today'     => 'Hari Ini / Today',
        'yesterday' => 'Kemarin / Yesterday',
        'older'     => 'Sebelumnya / Earlier',
    ];
    $initials = fn ($name) => collect(explode(' ', trim($name ?? '')))
        ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

<div class="relative" id="notif-root">
    {{-- Tombol lonceng --}}
    <button type="button" id="notif-toggle" aria-expanded="false" aria-label="Notifikasi"
            class="relative flex h-10 w-10 items-center justify-center rounded-full border border-white/30 text-white transition hover:bg-white/10">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
        </svg>
        @if ($notifUnread > 0)
            <span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-[#D5FC55] ring-2 ring-[#745BB8]"></span>
        @endif
    </button>

    {{-- Panel popup --}}
    <div id="notif-panel"
         class="absolute right-0 top-full z-50 mt-3 hidden w-[380px] max-w-[92vw] overflow-hidden rounded-2xl border border-slate-200 bg-white text-slate-800 shadow-2xl">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-2">
                <h3 class="text-lg font-bold text-slate-900">Notifikasi</h3>
                @if ($notifUnread > 0)
                    <span class="rounded-full bg-slate-900 px-2.5 py-0.5 text-xs font-semibold text-white">
                        {{ $notifUnread }} Baru
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if ($notifUnread > 0)
                    <form method="POST" action="{{ route('customer.notifikasi.read-all') }}">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-[#6D28D9] hover:underline">
                            Tandai dibaca
                        </button>
                    </form>
                @endif
                <button type="button" id="notif-close" aria-label="Tutup"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-600 hover:bg-slate-100">
                    ✕
                </button>
            </div>
        </div>

        {{-- Daftar --}}
        <div class="max-h-[420px] overflow-y-auto">
            @foreach ($sections as $key => $label)
                @if (isset($notifItems[$key]))
                    <p class="border-b border-slate-200 bg-slate-50 px-5 py-2 text-xs font-bold uppercase tracking-wide text-slate-500">
                        {{ $label }}
                    </p>

                    @foreach ($notifItems[$key] as $n)
                        <a href="{{ route('customer.notifikasi.read', $n) }}"
                           class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 transition hover:bg-slate-50 {{ $n->isRead() ? '' : 'bg-violet-50/60' }}">

                            {{-- Dot belum dibaca --}}
                            <span class="mt-4 h-2 w-2 shrink-0 rounded-full {{ $n->isRead() ? 'bg-transparent' : 'bg-slate-900' }}"></span>

                            {{-- Avatar / ikon --}}
                            @if ($n->type === \App\Models\Notification::TYPE_NEW_CHAT)
                                @if (!empty($n->data['sender_avatar']))
                                    <img src="{{ $n->data['sender_avatar'] }}" alt=""
                                         class="h-10 w-10 shrink-0 rounded-full object-cover">
                                @else
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-bold text-[#5B21B6]">
                                        {{ $initials($n->data['sender_name'] ?? '') }}
                                    </span>
                                @endif
                            @else
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#D5FC55]/60 text-slate-900">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    </svg>
                                </span>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-900">{{ $n->title }}</p>
                                @if ($n->body)
                                    <p class="mt-0.5 line-clamp-2 text-sm text-slate-600">{{ $n->body }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-400">{{ $n->created_at->locale('id')->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                @endif
            @endforeach

            @if ($notifItems->isEmpty())
                <div class="px-5 py-12 text-center text-sm text-slate-500">
                    Belum ada notifikasi.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        const root   = document.getElementById('notif-root');
        const toggle = document.getElementById('notif-toggle');
        const panel  = document.getElementById('notif-panel');
        const close  = document.getElementById('notif-close');

        const setOpen = (open) => {
            panel.classList.toggle('hidden', !open);
            toggle.setAttribute('aria-expanded', open);
        };

        toggle.addEventListener('click', () => setOpen(panel.classList.contains('hidden')));
        close.addEventListener('click', () => setOpen(false));
        document.addEventListener('click', (e) => { if (!root.contains(e.target)) setOpen(false); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
    })();
</script>