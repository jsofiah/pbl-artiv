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

        <x-shared.ui.badge :variant="$status['variant']" size="sm" :pill="false">
            {{ $status['label'] }}
        </x-shared.ui.badge>
    </div>
@endif