@extends('layouts.customer')

@section('title', 'Detail Pesanan')

@php
    $statusMap = [
        'pending'          => ['bg-amber-50 text-amber-600',     'Menunggu Konfirmasi'],
        'waiting_designer' => ['bg-amber-50 text-amber-600',     'Mencari Desainer'],
        'in_progress'      => ['bg-blue-50 text-blue-600',       'Sedang Dikerjakan'],
        'deliverable_sent' => ['bg-violet-50 text-violet-600',   'Menunggu Review'],
        'revision_needed'  => ['bg-orange-50 text-orange-600',   'Perlu Revisi'],
        'review'           => ['bg-violet-50 text-violet-600',   'Menunggu Review'],
        'completed'        => ['bg-emerald-50 text-emerald-600', 'Selesai'],
        'cancelled'        => ['bg-red-50 text-red-600',         'Dibatalkan'],
    ];

    $logMap = [
        'pending'          => ['Menunggu Konfirmasi',  'bg-amber-100 text-amber-700'],
        'waiting_designer' => ['Mencari Desainer',      'bg-amber-100 text-amber-700'],
        'in_progress'      => ['Sedang Dikerjakan',     'bg-blue-100 text-blue-700'],
        'deliverable_sent' => ['Draf Dikirim',          'bg-violet-100 text-violet-700'],
        'revision_needed'  => ['Revisi Diminta',        'bg-orange-100 text-orange-700'],
        'completed'        => ['Pesanan Selesai',       'bg-emerald-100 text-emerald-700'],
        'cancelled'        => ['Pesanan Dibatalkan',    'bg-red-100 text-red-700'],
    ];

    $formatLogTime = function($date) {
        if (!$date) return '-';
        return $date->translatedFormat('d M Y, H:i') . ' WIB';
    };
@endphp

@section('content')
<div class="max-w-[1280px] mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-slate-900">Detail Pesanan</h1>
        <div class="w-14 h-1.5 bg-[#6D28D9] rounded-full mt-2 mb-4"></div>
        <p class="text-slate-500 max-w-2xl">
            Pantau perkembangan pengerjaan proyek dan komunikasi langsung dengan desainer.
        </p>
    </div>

    @php
        $customer = $order->customer;
        $designer = $order->designer;
        $stats    = $designer?->designerStats;
        $conv     = $order->conversation;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-[420px_1fr] gap-6 items-start">

        {{-- ============ KOLOM KIRI ============ --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <h2 class="text-xs font-bold tracking-wide text-slate-400 mb-4">LAYANAN TERPESAN</h2>
                <div class="flex gap-4">
                    <div class="w-14 h-14 rounded-xl bg-[#6D28D9] text-white font-bold flex items-center justify-center shrink-0 overflow-hidden">
                        @php
                            use App\Helpers\R2Helper;
                            $thumbUrl = R2Helper::url($order->product->thumbnail_url);
                        @endphp

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
                            @if ($order->is_express)
                                · Express
                            @endif
                        </p>
                        <span class="inline-block mt-2 px-3 py-1 rounded-lg bg-violet-50 text-[#6D28D9] border font-bold text-sm">
                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="border-t border-slate-100 mt-4 pt-4 flex items-center justify-between">
                    <span class="text-sm text-slate-500">Status Pengerjaan</span>
                    @php
                        [$cls, $label] = $statusMap[$order->status] ?? ['bg-slate-50 text-slate-500', $order->status];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $cls }} text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        {{ $label }}
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-bold tracking-wide text-slate-400">INFORMASI PENGERJAAN</h2>
                    @if ($order->is_express)
                        <span class="px-2.5 py-1 rounded-md bg-lime-100 text-lime-700 text-xs font-bold">
                            EXPRESS
                        </span>
                    @endif
                </div>

                @if ($designer)
                    @php $designerAvatar = \App\Helpers\R2Helper::url($designer->avatar_url); @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-violet-100 text-[#6D28D9] font-bold flex items-center justify-center overflow-hidden">
                            @if ($designerAvatar)
                                <img src="{{ $designerAvatar }}" alt="{{ $designer->full_name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($designer->full_name, 0, 1)) }}
                            @endif
                        </div>
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
            </div>

            @if ($order->brief_note)
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xs font-bold tracking-wide text-slate-400">BRIEF PESANAN KLIEN</h2>
                        <button type="button"
                                onclick="document.getElementById('brief-modal').classList.remove('hidden')"
                                class="text-xs font-semibold text-[#6D28D9] hover:underline">
                            Detail Penuh
                        </button>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-sm text-slate-600 leading-relaxed line-clamp-4">
                            {{ $order->brief_note }}
                        </p>
                    </div>
                </div>
            @endif

            @if ($order->references->isNotEmpty())
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xs font-bold tracking-wide text-slate-400">REFERENSI & LAMPIRAN AWAL</h2>
                        <span class="text-xs text-slate-400 font-medium">
                            {{ $order->references->count() }} Berkas
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach ($order->references as $ref)
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
                                    <a href="{{ route('customer.pesanan.reference.download', ['order' => $order->id, 'reference' => $ref->id]) }}"
                                        class="text-slate-400 hover:text-[#6D28D9] transition shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                        </svg>
                                    </a>
                                @else
                                    <a href="{{ $ref->external_url }}" target="_blank"
                                    class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9] transition shrink-0">
                                        Buka
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($order->logs->isNotEmpty())
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-xs font-bold tracking-wide text-slate-400">RIWAYAT PENGERJAAN</h2>
                        <span class="text-xs text-slate-400 font-medium">
                            {{ $order->logs->count() }} Aktivitas
                        </span>
                    </div>

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
                                        <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                                            {{ $log->note }}
                                        </p>
                                    @endif

                                    <p class="text-xs text-slate-400 mt-1">
                                        {{ $formatLogTime($log->created_at) }}
                                        @if ($log->actor)
                                            · oleh {{ $log->actor->full_name }}
                                        @endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

        </div>

        {{-- ============ KOLOM KANAN: CHAT ============ --}}
        <div class="lg:sticky lg:top-20 h-[calc(100vh-160px)]">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-full overflow-hidden">

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    @php $designerAvatar = \App\Helpers\R2Helper::url($designer?->avatar_url); @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-violet-100 text-[#6D28D9] font-bold flex items-center justify-center overflow-hidden">
                            @if ($designerAvatar)
                                <img src="{{ $designerAvatar }}" alt="{{ $designer->full_name }}" class="w-full h-full object-cover">
                            @else
                                {{ $designer ? strtoupper(substr($designer->full_name, 0, 1)) : '?' }}
                            @endif
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 leading-tight">
                                {{ $designer->full_name ?? 'Belum ada desainer' }}
                            </p>
                            <p class="text-xs text-emerald-600 font-medium">
                                {{ $designer ? 'Online' : '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex-1 min-h-0 overflow-y-auto px-6 py-5 space-y-4" id="chat-container">
                    @php $lastDate = null; @endphp
                    @forelse ($conv?->messages ?? [] as $msg)
                        @php
                            $isMe = $msg->sender_id === auth()->id();
                            $sender = $msg->sender;

                            $currentDate = $msg->created_at->toDateString();
                            $showDateDivider = $lastDate !== $currentDate;
                            $lastDate = $currentDate;

                            if ($showDateDivider) {
                                $msgDate   = $msg->created_at->copy();
                                $today     = now()->startOfDay();
                                $yesterday = now()->subDay()->startOfDay();
                                $msgDay    = $msgDate->copy()->startOfDay();

                                if ($msgDay->equalTo($today)) {
                                    $dateLabel = 'Hari Ini';
                                } elseif ($msgDay->equalTo($yesterday)) {
                                    $dateLabel = 'Kemarin';
                                } else {
                                    $dateLabel = $msgDate->translatedFormat('d F Y');
                                }
                            }
                        @endphp

                        @if ($showDateDivider)
                            <div class="flex items-center gap-3 py-2">
                                <div class="flex-1 h-px bg-slate-200"></div>
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-xs font-medium text-slate-500">
                                    {{ $dateLabel }}
                                </span>
                                <div class="flex-1 h-px bg-slate-200"></div>
                            </div>
                        @endif

                        @if ($isMe)
                            <div class="flex flex-col items-end" data-msg-date="{{ $msg->created_at->toDateString() }}">
                                <p class="text-xs font-semibold text-slate-500 mb-1.5">
                                    {{ $msg->created_at->format('H:i') }} WIB
                                    <span class="font-normal text-slate-400">Anda</span>
                                </p>

                                <div class="max-w-[80%] bg-[#6D28D9] text-white rounded-2xl rounded-tr-sm px-4 py-3">
                                    @if ($msg->type === 'deliverable')
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-0.5 rounded-md bg-lime-300 text-lime-900 text-[10px] font-bold">
                                                DELIVERABLE
                                            </span>
                                        </div>
                                    @endif
                                    <p class="text-sm leading-relaxed whitespace-pre-line">{{ $msg->body }}</p>

                                    @foreach ($msg->attachments as $att)
                                        <div class="mt-3 bg-white/10 border border-white/20 rounded-xl p-3 flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold truncate">{{ $att->file_name }}</p>
                                                <p class="text-xs text-violet-100">
                                                    {{ number_format(($att->file_size ?? 0) / 1024 / 1024, 1) }} MB
                                                </p>
                                            </div>
                                            <a href="{{ route('customer.pesanan.attachment.download', ['order' => $order->id, 'attachment' => $att->id]) }}"
                                            class="shrink-0 inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg bg-lime-300 text-lime-900 hover:bg-lime-400 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                                </svg>
                                                Unduh
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="max-w-[80%]" data-msg-date="{{ $msg->created_at->toDateString() }}">
                                <p class="text-xs font-semibold text-slate-500 mb-1.5">
                                    {{ $sender->full_name ?? 'Designer' }}
                                    <span class="font-normal text-slate-400">{{ $msg->created_at->format('H:i') }} WIB</span>
                                </p>
                                <div class="bg-slate-50 rounded-2xl rounded-tl-sm px-4 py-3">
                                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $msg->body }}</p>

                                    @foreach ($msg->attachments as $att)
                                        <div class="mt-3 bg-white border border-slate-200 rounded-xl p-3 flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $att->file_name }}</p>
                                                <p class="text-xs text-slate-400">
                                                    {{ number_format(($att->file_size ?? 0) / 1024 / 1024, 1) }} MB
                                                </p>
                                            </div>
                                            <a href="{{ route('customer.pesanan.attachment.download', ['order' => $order->id, 'attachment' => $att->id]) }}"
                                            class="shrink-0 inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg bg-lime-300 text-lime-900 hover:bg-lime-400 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                                </svg>
                                                Unduh
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="flex items-center justify-center h-full">
                            <p class="text-sm text-slate-400">Belum ada pesan.</p>
                        </div>
                    @endforelse
                </div>

                <div class="px-4">
                    @if ($errors->any())
                        <div id="error-notif" class="mt-3 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-red-700">Pesan tidak dapat dikirim</p>
                                <ul class="text-xs text-red-600 mt-1 list-disc list-inside space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button type="button"
                                    onclick="document.getElementById('error-notif').remove()"
                                    class="text-red-400 hover:text-red-600 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>


                <form method="POST"
                    action="{{ route('customer.pesanan.kirimPesan', $order->id) }}"
                    enctype="multipart/form-data"
                    class="border-t border-slate-100 px-4 py-3 relative">

                    @csrf

                    <div id="file-preview" class="hidden mb-2 px-2">
                        <div class="inline-flex items-center gap-2 bg-slate-100 rounded-full pl-3 pr-2 py-1.5 text-sm">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5l4-4a3 3 0 10-4.24-4.24l-6 6a3 3 0 000 4.24m3 3.5l-4 4a3 3 0 11-4.24-4.24l6-6a3 3 0 014.24 0"/>
                            </svg>
                            <span id="file-name" class="text-slate-700 max-w-[200px] truncate"></span>
                            <button type="button" onclick="clearFileInput()"
                                    class="w-6 h-6 rounded-full hover:bg-slate-200 flex items-center justify-center text-slate-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">

                        <button type="button"
                                onclick="document.getElementById('attachment-input').click()"
                                class="shrink-0 w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-[#6D28D9] transition"
                                title="Lampirkan file">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                            </svg>

                        </button>

                        <input type="file"
                            id="attachment-input"
                            name="attachment"
                            class="hidden"
                            accept="*/*"
                            onchange="showFilePreview(this)">

                        <input type="text"
                            name="isi"
                            id="chat-input"
                            value="{{ old('isi') }}"
                            placeholder="Tulis pesan atau diskusi dengan desainer..."
                            class="flex-1 bg-slate-50 rounded-full px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30"
                            required>

                        <button type="button"
                                onclick="document.getElementById('emoji-picker').classList.toggle('hidden')"
                                class="shrink-0 w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-[#6D28D9] transition"
                                title="Emoji">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                            </svg>

                        </button>

                        <button type="submit"
                                class="shrink-0 inline-flex items-center gap-1.5 bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-sm font-semibold px-4 py-2.5 rounded-full transition"
                                title="Kirim">
                            <span class="hidden sm:inline">Kirim</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z" />
                            </svg>

                        </button>

                        <div id="emoji-picker"
                            class="hidden absolute bottom-20 right-6 bg-white rounded-2xl shadow-xl border border-slate-200 p-3 z-40 w-[280px]">
                            <div class="grid grid-cols-8 gap-1 max-h-48 overflow-y-auto">
                                @foreach (['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕','🤑','🤠','😈','👿','👹','👺','🤡','💩','👻','💀','👽','👾','🤖','🎃'] as $emoji)
                                    <button type="button"
                                            onclick="insertEmoji('{{ $emoji }}')"
                                            class="w-8 h-8 rounded-lg hover:bg-slate-100 text-lg flex items-center justify-center transition">
                                        {{ $emoji }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    <p class="text-xs text-slate-400 mt-2 ml-1">
                        Format didukung: PNG, JPG, ZIP, PDF (Maks. 25 MB)
                    </p>
                </form>
            </div>
        </div>

    </div>
</div>


@if ($order->brief_note)
    <div id="brief-modal"
            class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            onclick="if(event.target === this) this.classList.add('hidden')">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col">

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900">Brief Pesanan Klien</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $order->product->name }} · {{ $order->order_code }}
                    </p>
                </div>
                <button type="button"
                        onclick="document.getElementById('brief-modal').classList.add('hidden')"
                        class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $order->brief_note }}
                </p>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
                <button type="button"
                        onclick="document.getElementById('brief-modal').classList.add('hidden')"
                        class="px-5 py-2 rounded-xl bg-[#6D28D9] text-white text-sm font-semibold hover:bg-[#5B21B6] transition">
                    Tutup
                </button>
            </div>

        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const conversationId = '{{ $order->conversation?->id }}';

    if (!conversationId) return;

    window.Echo.private(`chat.${conversationId}`)
        .listen('.message.sent', (e) => {
            // Cek: kalau ini pesan kita sendiri, skip (biar tidak dobel)
            if (e.sender.id === '{{ auth()->id() }}') return;

            appendMessage(e);
        });
});

function appendMessage(e) {
    const container = document.getElementById('chat-container');
    // ... render bubble baru di sini
}

function showFilePreview(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const preview = document.getElementById('file-preview');
        const nameEl = document.getElementById('file-name');

        nameEl.textContent = file.name + ' (' + formatBytes(file.size) + ')';
        preview.classList.remove('hidden');
    }
}

function clearFileInput() {
    document.getElementById('attachment-input').value = '';
    document.getElementById('file-preview').classList.add('hidden');
}

function insertEmoji(emoji) {
    const input = document.getElementById('chat-input');
    const start = input.selectionStart;
    const end = input.selectionEnd;

    input.value = input.value.substring(0, start) + emoji + input.value.substring(end);
    input.selectionStart = input.selectionEnd = start + emoji.length;
    input.focus();
}

function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
}

document.addEventListener('click', (e) => {
    const picker = document.getElementById('emoji-picker');
    const btn = e.target.closest('button[title="Emoji"]');
    if (picker && !picker.contains(e.target) && !btn) {
        picker.classList.add('hidden');
    }
});

</script>
@endpush