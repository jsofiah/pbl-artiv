@extends('layouts.admin')

@section('title', 'Monitoring Pesanan & Pembayaran')

@section('content')
<div x-data="{ 
    tab: '{{ request('tab', 'pekerjaan') }}',
    subtab: 'pending',
    openModal: false, 
    activeImage: '', 
    activeOrderCode: '', 
    formApproveUrl: '', 
    formRejectUrl: '',
    confirmModalOpen: false,
    confirmUrl: '',
    confirmAction: '',
    confirmOrderCode: ''
}" class="space-y-6">

    {{-- Header Halaman --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-2">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Monitoring Pesanan</h1>
            <p class="text-slate-500 mt-1.5">Pantau progress pekerjaan desainer dan verifikasi bukti pembayaran customer.</p>
        </div>
    </div>

    {{-- Flash Message Alert --}}
    @if(session('success'))
        <div class="px-4 py-3 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="px-4 py-3 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
        </div>
    @endif

    @if(session('warning'))
        <div class="px-4 py-3 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('warning') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700">&times;</button>
        </div>
    @endif

    {{-- Card Statistik Ringkas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-2">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Butuh Verifikasi</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['pending_payments'] ?? 0 }}</h3>
                <p class="text-sm text-amber-600 font-medium">Bukti transfer baru</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-[#6D28D9] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Pesanan Berjalan</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['active_orders'] ?? 0 }}</h3>
                <p class="text-sm text-[#6D28D9] font-medium">Sedang diproses</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Pesanan Selesai</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['completed_orders'] ?? 0 }}</h3>
                <p class="text-sm text-emerald-600 font-medium">Selesai di-approve</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Lewat Deadline</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['late_orders'] ?? 0 }}</h3>
                <p class="text-sm text-rose-600 font-medium">Perlu perhatian</p>
            </div>
        </div>
    </div>

    {{-- Main Tab Switcher Utama (Garis Bawah) --}}
    <div class="border-b border-slate-200 mb-6 flex items-center gap-8">
        <button type="button" @click="tab = 'pekerjaan'" 
                :class="tab === 'pekerjaan' ? 'border-[#6D28D9] text-[#6D28D9]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2">
            Progress Pekerjaan
        </button>

        <button type="button" @click="tab = 'pembayaran'" 
                :class="tab === 'pembayaran' ? 'border-[#6D28D9] text-[#6D28D9]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2 relative">
            Verifikasi Pembayaran
            @if(($stats['pending_payments'] ?? 0) > 0)
                <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                    {{ $stats['pending_payments'] }}
                </span>
            @endif
        </button>
        @php
            $refunds = \App\Models\OrderRefund::with(['order.customer', 'order.designer'])
                ->where('status', \App\Models\OrderRefund::STATUS_PENDING)
                ->latest()
                ->get();
        @endphp

        <button type="button"
                @click="tab = 'refund'"
                :class="tab === 'refund' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2 relative">
            Refund
            @if($refunds->count() > 0)
                <span class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                    {{ $refunds->count() }}
                </span>
            @endif
        </button>
    </div>

    {{-- ==================== TAB 1: PROGRESS PEKERJAAN ==================== --}}
    <div x-show="tab === 'pekerjaan'" x-cloak>
        <form action="{{ route('admin.monitoring') }}" method="GET" class="flex flex-wrap items-center justify-end gap-3 mb-6 w-full">
            <input type="hidden" name="tab" value="pekerjaan">

            {{-- Search Bar (Ukuran disamakan dengan Katalog) --}}
            <div class="bg-white rounded-full shadow-sm border border-slate-200 p-1.5 flex items-center gap-2 w-full flex-1">
                <div class="pl-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari order, customer..."
                    class="flex-1 bg-transparent border-none focus:outline-none text-slate-700 placeholder:text-slate-400 px-1 py-1.5 text-sm">
                <button type="submit" class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-4 py-1.5 rounded-full transition text-sm whitespace-nowrap">
                    Cari
                </button>
            </div>

            {{-- Filter Status Order --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-4 pr-3 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[190px] justify-between">
                    <span>
                        @php
                            $statusLabels = [
                                '' => 'Semua Status',
                                'pending' => 'Menunggu Konfirmasi',
                                'waiting_designer' => 'Mencari Kreator',
                                'in_progress' => 'Sedang Dikerjakan',
                                'revision_needed' => 'Revisi Diperlukan',
                                'deliverable_sent' => 'Hasil Dikirim',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ];
                        @endphp
                        {{ $statusLabels[request('status_order')] ?? 'Semua Status' }}
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 right-0" style="display: none;">
                    @foreach ($statusLabels as $val => $label)
                        <a href="{{ route('admin.monitoring', array_merge(request()->except('status_order', 'page'), ['tab' => 'pekerjaan', 'status_order' => $val])) }}" 
                        class="block w-full text-left px-5 py-2 text-sm transition {{ request('status_order') === $val ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Urutkan --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-4 pr-3 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[190px] justify-between">
                    <span>
                        @php
                            $sortLabels = [
                                'terbaru' => 'Terbaru',
                                'terlama' => 'Terlama',
                                'deadline' => 'Mendekati Deadline',
                            ];
                        @endphp
                        Urutkan: {{ $sortLabels[request('sort')] ?? 'Terbaru' }}
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 right-0" style="display: none;">
                    @foreach ($sortLabels as $val => $label)
                        <a href="{{ route('admin.monitoring', array_merge(request()->except('sort', 'page'), ['tab' => 'pekerjaan', 'sort' => $val])) }}" 
                        class="block w-full text-left px-5 py-2 text-sm transition {{ (request('sort') ?? 'terbaru') === $val ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </form>

        {{-- Tabel Pekerjaan --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode & Produk</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Desainer</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Total Harga</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Tenggat Waktu</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Status Pengerjaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900 text-sm">{{ $order->order_code }}</div>
                                    <div class="text-slate-500 mt-0.5 text-xs">
                                        {{ $order->product->name ?? 'Produk' }} 
                                        @if($order->productTier)
                                            <span class="text-[10px] text-[#6D28D9] bg-purple-50 px-2 py-0.5 rounded font-medium">
                                                {{ $order->productTier->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 text-sm">{{ $order->customer->full_name ?? '-' }}</div>
                                    <div class="text-slate-400 text-xs">{{ $order->customer->email ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($order->designer)
                                        <div class="font-medium text-slate-900 text-sm">{{ $order->designer->full_name }}</div>
                                        <div class="text-xs text-emerald-600">Telah ditugaskan</div>
                                    @else
                                        <span class="inline-flex items-center text-amber-600 bg-amber-50 px-2 py-1 rounded-full text-xs font-medium">
                                            Belum Ada Desainer
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900 text-sm text-center">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    @if($order->deadline)
                                        <div class="{{ $order->isDeadlineMissed() ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                            {{ $order->deadline->translatedFormat('d M Y') }}<br>
                                            <span class="text-xs">{{ $order->deadline->format('H:i') }}</span>
                                        </div>
                                        @if($order->isDeadlineMissed())
                                            <span class="text-[10px] text-rose-500 font-semibold">(Lewat)</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $statusClass = match($order->status) {
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'revision_needed' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'deliverable_sent' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'cancelled', 'refunded' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-amber-50 text-amber-700 border-amber-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                                        {{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-slate-400">
                                    Tidak ada data pesanan/pekerjaan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 pt-5 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap p-4">
                {{-- Teks info selalu muncul, baik datanya 1 halaman maupun banyak --}}
                <p class="text-sm text-slate-500">
                    Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} data
                </p>

                {{-- Tombol navigasi hanya muncul jika lebih dari 1 halaman --}}
                @if($orders->hasPages())
                    <div class="flex items-center gap-2">
                        <a href="{{ $orders->previousPageUrl() ?? '#' }}"
                            class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $orders->onFirstPage() ? 'text-slate-300 pointer-events-none' : 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' }} transition">
                            &lt; Sebelumnya
                        </a>
                        @for ($i = 1; $i <= $orders->lastPage(); $i++)
                            <a href="{{ $orders->url($i) }}"
                                class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-semibold transition
                                        {{ $i === $orders->currentPage()
                                            ? 'bg-[#6D28D9] text-white shadow-sm'
                                            : 'border border-slate-200 bg-white text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                                {{ $i }}
                            </a>
                        @endfor
                        <a href="{{ $orders->nextPageUrl() ?? '#' }}"
                            class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $orders->hasMorePages() ? 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' : 'text-slate-300 pointer-events-none' }} transition">
                            Selanjutnya &gt;
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ==================== TAB 2: VERIFIKASI PEMBAYARAN ==================== --}}
    <div x-show="tab === 'pembayaran'" x-cloak>
        {{-- Subtab Filter (Pill Style) --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="subtab = 'pending'" 
                        :class="subtab === 'pending' ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]'"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition">
                    Menunggu Verifikasi
                </button>
                <button type="button" @click="subtab = 'berhasil'" 
                        :class="subtab === 'berhasil' ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]'"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition">
                    Berhasil / ACC
                </button>
                <button type="button" @click="subtab = 'gagal'" 
                        :class="subtab === 'gagal' ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]'"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition">
                    Ditolak
                </button>
                <button type="button" @click="subtab = 'semua'" 
                        :class="subtab === 'semua' ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]'"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition">
                    Semua Riwayat
                </button>
            </div>
        </div>

        {{-- Tabel Pembayaran --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Kode Order</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pembeli</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Tipe & Metode</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Nominal</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Bukti Transfer</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $payment)
                            <tr x-show="subtab === 'semua' || subtab === '{{ $payment->status }}'" 
                                x-cloak
                                class="hover:bg-slate-50/50 transition">
                                <td class="px-4 py-3 font-bold text-slate-900 text-center">
                                    {{ $payment->order->order_code ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 text-sm">{{ $payment->order->customer->full_name ?? 'Customer' }}</div>
                                    <div class="text-slate-400 text-xs">{{ $payment->order->customer->email ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="capitalize font-medium text-slate-800 text-sm">
                                        {{ str_replace('_', ' ', $payment->type) }}
                                    </span>
                                    <div class="text-slate-400 text-xs uppercase mt-0.5">{{ $payment->method }}</div>
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-900 text-sm text-center">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($payment->proof_url_temp)
                                        <button type="button" 
                                                @click="openModal = true; 
                                                        activeImage = '{{ $payment->proof_url_temp }}'; 
                                                        activeOrderCode = '{{ $payment->order->order_code ?? '' }}';
                                                        formApproveUrl = '{{ route('admin.monitoring.pembayaran.approve', $payment->id) }}';
                                                        formRejectUrl = '{{ route('admin.monitoring.pembayaran.reject', $payment->id) }}';"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#6D28D9] bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-full transition">
                                            Lihat Foto
                                        </button>
                                    @else
                                        <span class="text-slate-400 italic text-xs">Tidak ada foto</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($payment->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu ACC
                                        </span>
                                    @elseif($payment->status === 'berhasil')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Berhasil
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($payment->isPending())
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" 
                                                    @click="confirmModalOpen = true; 
                                                            confirmUrl = '{{ route('admin.monitoring.pembayaran.approve', $payment->id) }}'; 
                                                            confirmAction = 'ACC'; 
                                                            confirmOrderCode = '{{ $payment->order->order_code ?? '' }}';"
                                                    class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-full transition shadow-sm">
                                                ACC
                                            </button>

                                            <button type="button" 
                                                    @click="confirmModalOpen = true; 
                                                            confirmUrl = '{{ route('admin.monitoring.pembayaran.reject', $payment->id) }}'; 
                                                            confirmAction = 'Tolak'; 
                                                            confirmOrderCode = '{{ $payment->order->order_code ?? '' }}';"
                                                    class="px-3 py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-full transition shadow-sm">
                                                Tolak
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-slate-400">
                                    Tidak ada data pembayaran yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div x-show="tab === 'refund'" x-cloak>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Kode Order</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Bank Tujuan</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nominal</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($refunds as $refund)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    {{ $refund->order->order_code ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900">{{ $refund->order->customer->full_name ?? '-' }}</div>
                                    <div class="text-slate-400 text-xs">{{ $refund->order->customer->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $refund->bank_name }}</div>
                                    <div class="text-slate-500 text-xs">{{ $refund->bank_account_number }}</div>
                                    <div class="text-slate-400 text-xs">a.n. {{ $refund->bank_account_name }}</div>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    Rp {{ number_format($refund->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ ucfirst($refund->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if(!$refund->isCompleted())
                                        <button type="button"
                                                onclick="document.getElementById('modal-refund-{{ $refund->id }}').classList.remove('hidden')"
                                                class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm">
                                            Selesaikan Refund
                                        </button>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-slate-400 text-xs">Selesai</span>
                                            @if($refund->proof_url)
                                                <button type="button"
                                                        onclick="previewRefundProof('{{ \App\Helpers\R2Helper::url($refund->proof_url) }}')"
                                                        class="text-xs font-semibold text-purple-600 hover:underline">
                                                    Lihat Bukti
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-slate-400">
                                    Tidak ada pengajuan refund.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="openModal" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/70 backdrop-blur-md flex items-center justify-center p-4 sm:p-6"
             style="display: none;">

            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative border border-slate-100" @click.away="openModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Foto Bukti Transfer</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Order Code: <span class="font-bold text-[#6D28D9]" x-text="activeOrderCode"></span></p>
                    </div>
                    <button @click="openModal = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-full hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex justify-center bg-slate-50 rounded-2xl p-2 border border-slate-100 max-h-[60vh] overflow-auto">
                    <img :src="activeImage" alt="Bukti Transfer" class="max-w-full h-auto rounded-xl object-contain shadow-sm border border-slate-200">
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                    <button @click="openModal = false" type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-full transition">
                        Tutup
                    </button>

                    <template x-if="formRejectUrl">
                        <button type="button" 
                                @click="openModal = false; confirmModalOpen = true; 
                                        confirmUrl = formRejectUrl; 
                                        confirmAction = 'Tolak'; 
                                        confirmOrderCode = activeOrderCode;"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-full transition shadow-sm">
                            Tolak
                        </button>
                    </template>

                    <template x-if="formApproveUrl">
                        <button type="button" 
                                @click="openModal = false; confirmModalOpen = true; 
                                        confirmUrl = formApproveUrl; 
                                        confirmAction = 'ACC'; 
                                        confirmOrderCode = activeOrderCode;"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-full transition shadow-sm">
                            ACC Pembayaran
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="confirmModalOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">

            <div @click.away="confirmModalOpen = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-xl space-y-4 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto text-2xl font-bold"
                     :class="confirmAction === 'ACC' ? 'bg-emerald-50 text-emerald-500' : 'bg-red-50 text-red-500'">
                    <svg x-show="confirmAction === 'ACC'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg x-show="confirmAction === 'Tolak'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Konfirmasi <span x-text="confirmAction"></span></h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Apakah Anda yakin ingin <span x-text="confirmAction === 'ACC' ? 'menerima (ACC)' : 'menolak'"></span> pembayaran untuk order <strong class="text-slate-800" x-text="confirmOrderCode"></strong>?
                    </p>
                </div>

                <form :action="confirmUrl" method="POST" class="flex items-center justify-center gap-2 pt-2">
                    @csrf
                    @method('PATCH')
                    <button type="button" @click="confirmModalOpen = false" class="w-1/2 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-full transition">
                        Batal
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 text-white text-sm font-semibold rounded-full transition shadow-sm"
                            :class="confirmAction === 'ACC' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'">
                        Ya, Lanjutkan
                    </button>
                </form>
            </div>
        </div>
    </template>

</div>

@foreach($refunds as $refund)
    @if(!$refund->isCompleted())
        <div id="modal-refund-{{ $refund->id }}" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Selesaikan Refund</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Order: <strong>{{ $refund->order->order_code }}</strong> ·
                            Rp {{ number_format($refund->amount, 0, ',', '.') }}
                        </p>
                    </div>
                    <button type="button"
                            onclick="document.getElementById('modal-refund-{{ $refund->id }}').classList.add('hidden')"
                            class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="bg-slate-50 rounded-xl p-3 mb-4 text-xs">
                    <p class="text-slate-500 mb-1">Transfer ke:</p>
                    <p class="font-bold text-slate-900">{{ $refund->bank_name }} · {{ $refund->bank_account_number }}</p>
                    <p class="text-slate-600">a.n. {{ $refund->bank_account_name }}</p>
                </div>

                <form action="{{ route('admin.monitoring.refund.selesai', $refund->id) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      id="form-refund-{{ $refund->id }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Bukti Transfer Refund <span class="text-rose-500">*</span>
                        </label>
                        <input type="file"
                               name="proof"
                               accept="image/png,image/jpeg,image/webp,application/pdf"
                               required
                               class="w-full text-sm border border-slate-200 rounded-xl file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-[#6D28D9] hover:file:bg-violet-100">
                        <p class="text-xs text-slate-400 mt-1.5">
                            Format: PNG, JPG, WEBP, PDF · Maks. 5 MB
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <button type="button"
                                onclick="document.getElementById('modal-refund-{{ $refund->id }}').classList.add('hidden')"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm">
                            Selesaikan & Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endforeach

<div id="modal-preview-refund" class="hidden fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" onclick="if(event.target === this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
            <p class="font-bold text-slate-900 text-sm">Bukti Transfer Refund</p>
            <button type="button" onclick="document.getElementById('modal-preview-refund').classList.add('hidden')"
                    class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-slate-50">
            <img id="preview-refund-img" src="" alt="Bukti" class="max-w-full max-h-[70vh] rounded-lg shadow">
        </div>
    </div>
</div>

<script>
function previewRefundProof(url) {
    document.getElementById('preview-refund-img').src = url;
    document.getElementById('modal-preview-refund').classList.remove('hidden');
}
</script>
@endsection