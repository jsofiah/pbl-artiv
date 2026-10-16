@props([
    'deliverable' => null,
    'isMe'        => false,
])

@if ($deliverable)
    @php
        $statusMap = [
            'pending'            => ['variant' => 'solid-amber',   'label' => 'Menunggu Review'],
            'approved'           => ['variant' => 'solid-emerald', 'label' => 'Disetujui'],
            'revision_requested' => ['variant' => 'solid-amber',   'label' => 'Revisi Diminta'],
        ];

        $status = $statusMap[$deliverable->status] ?? ['variant' => 'slate', 'label' => $deliverable->status];
    @endphp

    <div class="flex items-center gap-2 mb-2">
        <x-shared.ui.badge
            :variant="$isMe ? 'solid-lime' : 'solid-violet'"
            size="sm"
            :pill="false">
            DELIVERABLE
        </x-shared.ui.badge>

        <span data-deliverable-badge
              class="inline-flex items-center gap-1.5 border font-semibold rounded-md text-[10px] px-2 py-0.5
                     @if($status['variant'] === 'solid-amber') bg-amber-400 text-amber-900 border-transparent
                     @elseif($status['variant'] === 'solid-emerald') bg-emerald-500 text-white border-transparent
                     @else bg-slate-100 text-slate-600 border-slate-200
                     @endif">
            {{ $status['label'] }}
        </span>
    </div>
@endif