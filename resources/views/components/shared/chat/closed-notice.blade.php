@props(['status' => null])

<div class="border-t border-slate-100 px-4 py-5 bg-slate-50">
    <div class="flex items-start gap-3 text-sm text-slate-500">
        <svg class="w-5 h-5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-slate-700">Percakapan telah ditutup</p>
            <p class="text-xs mt-1">
                @if ($status === 'completed')
                    Pesanan sudah selesai dan masa tenggang percakapan telah berakhir.
                @elseif ($status === 'cancelled')
                    Pesanan telah dibatalkan.
                @else
                    Pesanan telah di-refund.
                @endif
                Anda masih bisa membaca riwayat chat di atas.
            </p>
        </div>
    </div>
</div>