@props([
    'order',
    'context' => 'default',
])

@php
    $statusMap = [
        'pending'          => ['bg-amber-50 text-amber-700',    'Menunggu'],
        'waiting_designer' => ['bg-amber-50 text-amber-700',    'Mencari Designer'],
        'in_progress'      => ['bg-blue-50 text-blue-700',      'Dikerjakan'],
        'deliverable_sent' => ['bg-violet-50 text-violet-700',  'Menunggu Review'],
        'revision_needed'  => ['bg-orange-50 text-orange-700',  'Perlu Revisi'],
        'completed'        => ['bg-emerald-50 text-emerald-700','Selesai'],
        'cancelled'        => ['bg-red-50 text-red-700',        'Batal'],
    ];
    [$statusCls, $statusLabel] = $statusMap[$order->status]
        ?? ['bg-slate-100 text-slate-600', ucfirst($order->status)];

    $thumbUrl = $order->product
        ? \App\Helpers\R2Helper::url($order->product->thumbnail_url)
        : null;

    $isUrgent = $context === 'job-pool' && $order->is_urgent;
@endphp

<div class="bg-white rounded-2xl border {{ $isUrgent ? 'border-red-300 shadow-md' : 'border-slate-100 shadow-sm' }} overflow-hidden flex flex-col">

    <div class="relative h-40 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center overflow-hidden">
        @if ($thumbUrl)
            <img src="{{ $thumbUrl }}"
                 alt="{{ $order->product->name ?? 'Preview' }}"
                 class="w-full h-full object-cover">
        @else
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 16l5-5a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L21 15M3 6h18v13H3V6z"/>
            </svg>
        @endif

        @if ($isUrgent)
            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-red-500 text-white text-[11px] font-bold tracking-wide flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
                URGENT
            </span>
        @endif

        <span class="absolute top-3 right-3 px-2.5 py-1 rounded-md {{ $statusCls }} text-[11px] font-bold tracking-wide">
            {{ $statusLabel }}
        </span>
    </div>

    <div class="p-4 flex flex-col flex-1">
        <p class="text-xs font-semibold text-[#6D28D9] mb-1 truncate">
            {{ $order->order_code }}
        </p>
        <h3 class="font-bold text-slate-900 leading-snug mb-2 line-clamp-2">
            {{ $order->product->name ?? 'Layanan tidak tersedia' }}
        </h3>
        <p class="text-sm text-slate-500 leading-relaxed mb-4">
            {{ $order->customer->full_name ?? 'Customer' }}
            • {{ $order->productTier->name ?? '-' }}
            @if ($order->is_express)
                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 ml-1">
                    EXPRESS
                </span>
            @endif
        </p>

        <div class="mt-auto">
            <div class="flex items-center gap-2 text-xs text-slate-500 border-t border-slate-100 pt-3">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <path stroke-linecap="round" d="M8 2v4M16 2v4M3 9h18"/>
                </svg>
                Tenggat:
                <span class="font-medium text-slate-700">
                    {{ $order->deadline?->translatedFormat('d M Y') ?? '-' }}
                </span>
            </div>

            <div class="flex items-center justify-between text-sm mt-2.5 mb-4">
                <span class="text-slate-500">Biaya:</span>
                <span class="font-bold text-slate-900">
                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                </span>
            </div>

            <div>
                {{ $action ?? '' }}
            </div>
        </div>
    </div>
</div>