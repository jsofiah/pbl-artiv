@props([
    'deliverable' => null,
    'isDesigner'  => false,
    'order'       => null,
])

@if ($deliverable)
    @if ($isDesigner && $order && $deliverable->status === 'pending')
        @php
            $limit     = \App\Models\Order::FREE_REVISION_LIMIT;
            $used      = $order->revision_count;
            $remaining = max(0, $limit - $used);
        @endphp

        @if ($used > 0)
            <div class="mt-3 p-3 rounded-xl
                        {{ $remaining === 0 ? 'bg-red-50 border border-red-200' : ($remaining === 1 ? 'bg-amber-50 border border-amber-200' : 'bg-slate-50 border border-slate-200') }}
                        flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0
                            {{ $remaining === 0 ? 'text-red-600' : ($remaining === 1 ? 'text-amber-600' : 'text-slate-500') }}"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs
                          {{ $remaining === 0 ? 'text-red-800' : ($remaining === 1 ? 'text-amber-800' : 'text-slate-600') }}">
                    Customer sudah pakai <strong>{{ $used }} dari {{ $limit }}</strong> revisi gratis.
                    @if ($remaining > 0)
                        Sisa <strong>{{ $remaining }}</strong>.
                    @else
                        <span class="font-semibold">Ini deliverable terakhir — customer hanya bisa Approve.</span>
                    @endif
                </p>
            </div>
        @endif
    @endif

    @if ($deliverable->status === 'revision_requested' && $deliverable->revision_note)
        <div class="mt-3 p-3 rounded-xl bg-orange-50 border border-orange-200">
            <p class="text-xs font-bold text-orange-700 mb-1">
                {{ $isDesigner ? 'Catatan Revisi dari Customer:' : 'Catatan Revisi Anda:' }}
            </p>
            <p class="text-sm text-orange-800 whitespace-pre-line">{{ $deliverable->revision_note }}</p>
        </div>
    @endif

    @if ($deliverable->status === 'approved')
        <div class="mt-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-semibold text-emerald-700">
                @if ($isDesigner)
                    Disetujui customer pada {{ $deliverable->approved_at?->translatedFormat('d M Y, H:i') }} WIB
                @else
                    Anda menyetujui hasil desain ini pada {{ $deliverable->approved_at?->translatedFormat('d M Y, H:i') }} WIB
                @endif
            </p>
        </div>
    @endif
@endif