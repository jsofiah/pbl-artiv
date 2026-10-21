@props(['chatClosesAt'])

<div class="px-4 pt-3">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
        </svg>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-amber-800">Pesanan telah selesai</p>
            <p class="text-xs text-amber-700 mt-0.5">
                Percakapan masih terbuka sampai
                <strong>{{ $chatClosesAt->translatedFormat('d M Y, H:i') }} WIB</strong>
                ({{ $chatClosesAt->diffForHumans(now()) }}).
                Setelah itu chat hanya bisa dibaca.
            </p>
        </div>
    </div>
</div>