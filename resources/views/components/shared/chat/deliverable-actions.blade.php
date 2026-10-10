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
@endphp

@if ($deliverable->status === 'pending')
    <div class="mt-4 pt-3 border-t border-slate-200 flex flex-wrap gap-2">
        <form method="POST"
              action="{{ route($approveRoute, ['order' => $order->id, 'deliverable' => $deliverable->id]) }}"
              onsubmit="return confirm('Setujui hasil desain ini? Pesanan akan ditandai selesai.')">
            @csrf
            <x-shared.ui.button type="submit" variant="success" icon="check">
                Approve
            </x-shared.ui.button>
        </form>

        <x-shared.ui.button
            variant="outline-warning"
            icon="rotate"
            onclick="openRevisionModal('{{ $deliverable->id }}')">
            Minta Revisi
        </x-shared.ui.button>
    </div>
@endif

@if ($deliverable->status === 'revision_requested' && $deliverable->revision_note)
    <div class="mt-3 p-3 rounded-xl bg-orange-50 border border-orange-200">
        <p class="text-xs font-bold text-orange-700 mb-1">Catatan Revisi:</p>
        <p class="text-sm text-orange-800 whitespace-pre-line">{{ $deliverable->revision_note }}</p>
    </div>
@endif

@if ($deliverable->status === 'approved')
    <div class="mt-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-semibold text-emerald-700">
            Disetujui pada {{ $deliverable->approved_at?->translatedFormat('d M Y, H:i') }} WIB
        </p>
    </div>
@endif