@php
    $designer = $order->designer;
    $stats    = $designer?->designerStats;

    $statusMap = [
        'pending'                   => ['amber',   'Menunggu Konfirmasi'],
        'waiting_designer'          => ['amber',   'Mencari Kreator'],
        'in_progress'               => ['blue',    'Sedang Dikerjakan'],
        'deliverable_sent'          => ['violet',  'Menunggu Review'],
        'revision_needed'           => ['orange',  'Perlu Revisi'],
        'waiting_customer_decision' => ['orange',  'Menunggu Keputusan'],
        'refund_requested'          => ['red',     'Refund Diajukan'],
        'refunded'                  => ['slate',   'Refund Selesai'],
        'completed'                 => ['emerald', 'Selesai'],
        'cancelled'                 => ['red',     'Dibatalkan'],
    ];

    $logMap = [
        'pending'                   => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-700'],
        'waiting_designer'          => ['Mencari Kreator',     'bg-amber-100 text-amber-700'],
        'in_progress'               => ['Sedang Dikerjakan',   'bg-blue-100 text-blue-700'],
        'deliverable_sent'          => ['Draf Dikirim',        'bg-violet-100 text-violet-700'],
        'revision_needed'           => ['Revisi Diminta',      'bg-orange-100 text-orange-700'],
        'waiting_customer_decision' => ['Menunggu Keputusan',  'bg-orange-100 text-orange-700'],
        'refund_requested'          => ['Refund Diajukan',     'bg-red-100 text-red-700'],
        'refunded'                  => ['Refund Selesai',      'bg-slate-100 text-slate-700'],
        'completed'                 => ['Pesanan Selesai',     'bg-emerald-100 text-emerald-700'],
        'cancelled'                 => ['Pesanan Dibatalkan',  'bg-red-100 text-red-700'],
    ];

    $formatLogTime = function($date) {
        if (!$date) return '-';
        return $date->translatedFormat('d M Y, H:i') . ' WIB';
    };

    use App\Helpers\R2Helper;
    $thumbUrl = R2Helper::url($order->product->thumbnail_url);
    [$statusVariant, $statusLabel] = $statusMap[$order->status] ?? ['slate', $order->status];
@endphp

<div class="space-y-6">

    <x-shared.ui.card title="LAYANAN TERPESAN">
        <div class="flex gap-4">
            <div class="w-14 h-14 rounded-xl bg-[#6D28D9] text-white font-bold flex items-center justify-center shrink-0 overflow-hidden">
                @if ($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="{{ $order->product->name }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($order->product->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <h3 class="font-bold text-lg text-slate-900 leading-snug">
                    {{ $order->product->name }}
                </h3>
                <p class="text-sm text-slate-500 mt-0.5">
                    {{ $order->productTier->name }}
                    @if ($order->is_express) · Express @endif
                </p>
                <span class="inline-block mt-2 px-3 py-1 rounded-lg bg-violet-50 text-[#6D28D9] border font-bold text-sm">
                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="border-t border-slate-100 mt-4 pt-4 flex items-center justify-between">
            <span class="text-sm text-slate-500">Status Pengerjaan</span>
            <x-shared.ui.badge :variant="$statusVariant" dot>{{ $statusLabel }}</x-shared.ui.badge>
        </div>
    </x-shared.ui.card>

    <x-shared.ui.card title="INFORMASI PENGERJAAN">
        @if ($order->is_express)
            <x-slot:action>
                <span class="px-2.5 py-1 rounded-md bg-lime-100 text-lime-700 text-xs font-bold">
                    EXPRESS
                </span>
            </x-slot:action>
        @endif

        @if ($designer)
            <div class="flex items-center gap-3">
                <x-shared.ui.avatar :user="$designer" size="lg" />

                <div>
                    <p class="font-semibold text-slate-900">{{ $designer->full_name }}</p>
                    <p class="text-sm text-slate-500">
                        Rating: {{ number_format($stats->average_rating ?? 0, 1) }} ★
                        ({{ $stats->completed_orders ?? 0 }} pesanan)
                    </p>
                </div>
            </div>
        @else
            <div class="bg-slate-50 rounded-xl p-4 text-center">
                <p class="text-sm text-slate-500">Menunggu desainer mengambil pesanan ini.</p>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-3 mt-4">
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-500 mb-1">Target Tenggat</p>
                <p class="font-bold text-slate-900 text-sm">
                    {{ $order->deadline?->translatedFormat('d M Y') ?? '-' }}
                </p>
                <p class="text-xs text-slate-400">
                    {{ $order->deadline?->format('H:i') }} WIB
                </p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-500 mb-1">Sisa Waktu</p>
                @if ($order->deadline && $order->status !== 'completed')
                    <p class="font-bold text-[#6D28D9] text-sm">
                        {{ $order->deadline->diffForHumans(now(), true) }}
                    </p>
                    <p class="text-xs text-slate-400">
                        {{ $order->deadline->isFuture() ? 'On schedule' : 'Terlambat' }}
                    </p>
                @else
                    <p class="font-bold text-slate-400 text-sm">-</p>
                @endif
            </div>
        </div>
    </x-shared.ui.card>

    {{-- BRIEF PESANAN KLIEN --}}
    @if ($order->brief_note)
        <x-shared.ui.card title="BRIEF PESANAN KLIEN">
            <x-slot:action>
                <button type="button"
                        onclick="document.getElementById('brief-modal').classList.remove('hidden')"
                        class="text-xs font-semibold text-[#6D28D9] hover:underline">
                    Detail Penuh
                </button>
            </x-slot:action>

            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-slate-600 leading-relaxed line-clamp-4">
                    {{ $order->brief_note }}
                </p>
            </div>
        </x-shared.ui.card>
    @endif

    @if ($order->references->isNotEmpty())
        <x-shared.ui.card title="REFERENSI & LAMPIRAN AWAL">
            <x-slot:action>
                <span class="text-xs text-slate-400 font-medium">
                    {{ $order->references->count() }} Berkas
                </span>
            </x-slot:action>

            <div class="space-y-2">
                @foreach ($order->references as $ref)
                    @php
                        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
                        $isImage    = $ref->type === 'file' && in_array(strtolower($ref->mime_type ?? ''), $imageMimes);
                    @endphp

                    <div class="flex items-center justify-between bg-slate-50 rounded-xl p-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0">
                                @if ($ref->type === 'file')
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-6 4h6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5L17 7.5V19a2 2 0 01-2 2z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5l4-4a3 3 0 10-4.24-4.24l-6 6a3 3 0 000 4.24m3 3.5l-4 4a3 3 0 11-4.24-4.24l6-6a3 3 0 014.24 0"/>
                                    </svg>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">
                                    {{ $ref->file_name ?? $ref->external_url }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    @if ($ref->type === 'file')
                                        {{ number_format(($ref->file_size ?? 0) / 1024 / 1024, 1) }} MB
                                    @else
                                        Tautan Eksternal
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($ref->type === 'file' && $ref->file_url)
                            <div class="flex items-center gap-1 shrink-0">
                                @if ($isImage)
                                    <button type="button"
                                            onclick="previewImage('{{ route('customer.pesanan.reference.preview', ['order' => $order->id, 'reference' => $ref->id]) }}', '{{ addslashes($ref->file_name ?? 'Gambar') }}')"
                                            class="w-9 h-9 rounded-lg hover:bg-violet-50 text-slate-400 hover:text-[#6D28D9] transition flex items-center justify-center"
                                            title="Lihat gambar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </button>
                                @endif

                                <a href="{{ route('customer.pesanan.reference.download', ['order' => $order->id, 'reference' => $ref->id]) }}"
                                   class="w-9 h-9 rounded-lg hover:bg-violet-50 text-slate-400 hover:text-[#6D28D9] transition flex items-center justify-center"
                                   title="Unduh file">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                    </svg>
                                </a>
                            </div>
                        @else
                            <a href="{{ $ref->external_url }}" target="_blank"
                               class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9] transition shrink-0">
                                Buka
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-shared.ui.card>
    @endif

    @if ($order->logs->isNotEmpty())
        <x-shared.ui.card title="RIWAYAT PENGERJAAN">
            <x-slot:action>
                <span class="text-xs text-slate-400 font-medium">
                    {{ $order->logs->count() }} Aktivitas
                </span>
            </x-slot:action>

            <ol class="relative">
                @foreach ($order->logs as $log)
                    @php
                        [$logLabel, $logColor] = $logMap[$log->status] ?? ['Aktivitas', 'bg-slate-100 text-slate-600'];
                        $isLast = $loop->last;
                        $isCurrent = $isLast;
                    @endphp

                    <li class="flex gap-3 {{ !$isLast ? 'pb-6' : '' }} relative">
                        @if (!$isLast)
                            <span class="absolute left-[11px] top-6 bottom-0 w-px bg-slate-200"></span>
                        @endif

                        @if ($isCurrent)
                            <span class="w-6 h-6 rounded-full border-2 border-lime-400 bg-white flex items-center justify-center shrink-0 z-10">
                                <span class="w-2.5 h-2.5 rounded-full bg-lime-400"></span>
                            </span>
                        @else
                            <span class="w-6 h-6 rounded-full bg-[#6D28D9] text-white flex items-center justify-center shrink-0 z-10">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        @endif

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md {{ $logColor }} text-xs font-semibold">
                                    {{ $logLabel }}
                                </span>
                                @if ($isCurrent)
                                    <span class="text-[10px] font-bold text-lime-600 uppercase tracking-wide">
                                        Sekarang
                                    </span>
                                @endif
                            </div>

                            @if ($log->note)
                                <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">{{ $log->note }}</p>
                            @endif

                            <p class="text-xs text-slate-400 mt-1">
                                {{ $formatLogTime($log->created_at) }}
                                @if ($log->actor) · oleh {{ $log->actor->full_name }} @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-shared.ui.card>
    @endif

</div>