@php
    $customer = $order->customer;
    $product  = $order->product;

    $statusMap = [
        'pending'                   => ['amber',   'Menunggu Konfirmasi'],
        'in_progress'               => ['blue',    'Sedang Dikerjakan'],
        'deliverable_sent'          => ['violet',  'Menunggu Review'],
        'revision_needed'           => ['orange',  'Perlu Revisi'],
        'waiting_customer_decision' => ['orange',  'Menunggu Keputusan'],
        'refund_requested'          => ['red',     'Refund Diajukan'],
        'refunded'                  => ['slate',   'Refund Selesai'],
        'completed'                 => ['emerald', 'Selesai'],
        'cancelled'                 => ['red',     'Dibatalkan'],
    ];
    [$statusVariant, $statusLabel] = $statusMap[$order->status] ?? ['slate', ucfirst($order->status)];
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- 1. Layanan --}}
    <x-shared.ui.card title="LAYANAN">
        <div class="flex gap-3">
            <div class="w-12 h-12 rounded-xl bg-[#6D28D9] text-white font-bold flex items-center justify-center shrink-0 overflow-hidden">
                @php $thumbUrl = $product ? \App\Helpers\R2Helper::url($product->thumbnail_url) : null; @endphp
                @if ($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="" class="w-full h-full object-cover">
                @else
                    {{ $product ? strtoupper(substr($product->name, 0, 1)) : '?' }}
                @endif
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-slate-900 leading-snug line-clamp-2">
                    {{ $product->name ?? 'Layanan tidak tersedia' }}
                </p>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $order->productTier->name ?? '-' }}@if ($order->is_express) · Express @endif
                </p>
            </div>
        </div>

        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-500">Status</span>
            <x-shared.ui.badge :variant="$statusVariant" dot>{{ $statusLabel }}</x-shared.ui.badge>
        </div>

        <div class="mt-2 text-sm font-bold text-[#6D28D9]">
            Rp {{ number_format($order->total_price, 0, ',', '.') }}
        </div>
    </x-shared.ui.card>

    {{-- 2. Customer --}}
    <x-shared.ui.card title="CUSTOMER">
        @if ($order->is_express)
            <x-slot:action>
                <span class="px-2 py-0.5 rounded-md bg-lime-100 text-lime-700 text-[10px] font-bold">EXPRESS</span>
            </x-slot:action>
        @endif

        @if ($customer)
            <div class="flex items-center gap-3">
                <x-shared.ui.avatar :user="$customer" size="md" />
                <div class="min-w-0">
                    <p class="font-semibold text-slate-900 truncate">{{ $customer->full_name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $customer->email }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-2 mt-4">
            <div class="bg-slate-50 rounded-lg p-2.5">
                <p class="text-[10px] text-slate-500 mb-0.5">Tenggat</p>
                <p class="font-bold text-slate-900 text-xs">
                    {{ $order->deadline?->translatedFormat('d M') ?? '-' }}
                </p>
            </div>
            <div class="bg-slate-50 rounded-lg p-2.5">
                <p class="text-[10px] text-slate-500 mb-0.5">Sisa Waktu</p>
                @if ($order->deadline && $order->status !== 'completed')
                    <p class="font-bold text-[#6D28D9] text-xs">
                        {{ now()->diffForHumans($order->deadline, true) }}
                    </p>
                @else
                    <p class="font-bold text-slate-400 text-xs">-</p>
                @endif
            </div>
        </div>
    </x-shared.ui.card>

    {{-- 3. Brief & Referensi --}}
    <x-shared.ui.card title="BRIEF & REFERENSI">
        @if ($order->brief_note)
            <x-slot:action>
                <button type="button"
                        onclick="document.getElementById('brief-modal').classList.remove('hidden')"
                        class="text-xs font-semibold text-[#6D28D9] hover:underline">
                    Detail
                </button>
            </x-slot:action>
        @endif

        <div class="mb-3">
            @if ($order->brief_note)
                <p class="text-sm text-slate-600 leading-relaxed line-clamp-3">
                    {{ $order->brief_note }}
                </p>
            @else
                <p class="text-sm text-slate-400 italic">Tidak ada brief.</p>
            @endif
        </div>

        @if ($order->references->isNotEmpty())
            <div class="mt-auto pt-3 border-t border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wide">
                    {{ $order->references->count() }} Berkas Referensi
                </p>
                <div class="space-y-1">
                    @foreach ($order->references->take(2) as $ref)
                        <x-designer.pekerjaan.reference-item :ref="$ref" :order="$order" />
                    @endforeach
                    @if ($order->references->count() > 2)
                        <p class="text-[10px] text-slate-400 text-center pt-1">
                            +{{ $order->references->count() - 2 }} berkas lagi
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </x-shared.ui.card>

</div>