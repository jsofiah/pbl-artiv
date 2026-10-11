@props([
    'deliverable',
    'order',
    'routePrefix' => null,
])

@php
    if (!$routePrefix) {
        $routePrefix = request()->routeIs('designer.*') ? 'designer' : 'customer';
    }

    $approveRoute = "{$routePrefix}.pesanan.deliverable.approve";
    $revisiRoute  = "{$routePrefix}.pesanan.deliverable.revisi";

    $limit        = \App\Models\Order::FREE_REVISION_LIMIT;
    $used         = $order->revision_count;
    $remaining    = max(0, $limit - $used);
    $isLastChance = $used >= $limit;
@endphp

@if ($deliverable->status === 'pending')
    <div class="mt-4 pt-3 border-t border-slate-200 flex flex-wrap gap-2">

        {{-- Tombol Approve --}}
        {{-- Form hidden untuk submit approve --}}
        <form method="POST"
            id="approve-form-{{ $deliverable->id }}"
            action="{{ route($approveRoute, ['order' => $order->id, 'deliverable' => $deliverable->id]) }}"
            class="hidden">
            @csrf
        </form>

        {{-- Tombol Approve (buka modal) --}}
        <button type="button"
                onclick="openApproveModal('{{ $deliverable->id }}')"
                class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                    bg-[#D5FC55] text-neutral-900 text-sm font-bold
                    shadow-[0_2px_8px_-2px_rgba(213,252,85,0.5)]
                    hover:bg-[#c5ec45] hover:shadow-[0_4px_12px_-2px_rgba(213,252,85,0.6)]
                    hover:-translate-y-0.5 active:translate-y-0
                    transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            <span>Approve</span>
        </button>

        @if ($order->canRequestFreeRevision())
            <button type="button"
                    onclick="openRevisionModal('{{ $deliverable->id }}')"
                    class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                           bg-[#FBC003] text-neutral-900 text-sm font-bold
                           shadow-[0_2px_8px_-2px_rgba(251,192,3,0.5)]
                           hover:bg-[#e8b202] hover:shadow-[0_4px_12px_-2px_rgba(251,192,3,0.6)]
                           hover:-translate-y-0.5 active:translate-y-0
                           transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:rotate-180">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                <span>Minta Revisi</span>
                <span class="text-[10px] opacity-75 font-normal">({{ $remaining }} sisa)</span>
            </button>
        @endif
    </div>

    @if ($remaining === 1 && $order->canRequestFreeRevision())
        <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-2">
            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <div class="text-xs text-amber-800">
                <p class="font-semibold mb-0.5">Sisa 1 revisi gratis</p>
                <p class="text-amber-700 leading-relaxed">
                    Gunakan dengan bijak. Setelah revisi berikutnya, Anda hanya bisa menyetujui hasil desain.
                </p>
            </div>
        </div>
    @endif

    @if ($isLastChance)
        <div class="mt-3 p-3 rounded-xl bg-red-50 border border-red-200 flex items-start gap-2">
            <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <div class="text-xs text-red-800">
                <p class="font-semibold mb-0.5">Revisi gratis sudah habis</p>
                <p class="text-red-700 leading-relaxed">
                    Ini adalah hasil akhir dari desainer. Anda hanya bisa <strong>Approve</strong> untuk menyelesaikan pesanan.
                </p>
            </div>
        </div>
    @endif
@endif