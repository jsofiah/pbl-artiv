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

    // Modal Detail Monitoring
    detailModalOpen: false,
    detailLoading: false,
    detailData: null,

    async fetchDetail(url) {
        this.detailModalOpen = true;
        this.detailLoading = true;
        this.detailData = null;

        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            if (response.ok) {
                this.detailData = await response.json();
            } else {
                alert('Gagal mengambil data detail pengerjaan.');
                this.detailModalOpen = false;
            }
        } catch (e) {
            alert('Terjadi kesalahan koneksi.');
            this.detailModalOpen = false;
        } finally {
            this.detailLoading = false;
        }
    }
}">

    {{-- Flash Message Alert --}}
    @if(session('success'))
        <div class="mb-6 p-4 text-sm text-emerald-800 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 text-sm text-rose-800 bg-rose-50 rounded-2xl border border-rose-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 text-sm text-amber-800 bg-amber-50 rounded-2xl border border-amber-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('warning') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700">&times;</button>
        </div>
    @endif

    {{-- Card Statistik Ringkas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Butuh Verifikasi</p>
                <h3 class="text-xl font-bold text-gray-900 mt-0.5">{{ $stats['pending_payments'] ?? 0 }}</h3>
                <p class="text-[11px] text-amber-600 font-medium">Bukti transfer baru</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Pesanan Berjalan</p>
                <h3 class="text-xl font-bold text-gray-900 mt-0.5">{{ $stats['active_orders'] ?? 0 }}</h3>
                <p class="text-[11px] text-purple-600 font-medium">Sedang diproses</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Pesanan Selesai</p>
                <h3 class="text-xl font-bold text-gray-900 mt-0.5">{{ $stats['completed_orders'] ?? 0 }}</h3>
                <p class="text-[11px] text-emerald-600 font-medium">Selesai di-approve</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Lewat Deadline</p>
                <h3 class="text-xl font-bold text-gray-900 mt-0.5">{{ $stats['late_orders'] ?? 0 }}</h3>
                <p class="text-[11px] text-rose-600 font-medium">Perlu perhatian</p>
            </div>
        </div>
    </div>

    {{-- Main Tab Switcher Utama (Pekerjaan vs Pembayaran) --}}
    <div class="border-b border-gray-200 mb-6 flex items-center gap-8">
        <button type="button"
                @click="tab = 'pekerjaan'" 
                :class="tab === 'pekerjaan' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2">
            Progress Pekerjaan Desainer
        </button>

        <button type="button"
                @click="tab = 'pembayaran'" 
                :class="tab === 'pembayaran' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2 relative">
            Verifikasi Pembayaran
            @if(($stats['pending_payments'] ?? 0) > 0)
                <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                    {{ $stats['pending_payments'] }}
                </span>
            @endif
        </button>
    </div>

    {{-- ==================== TAB 1: PROGRESS PEKERJAAN ==================== --}}
    <div x-show="tab === 'pekerjaan'" x-cloak>
        <form action="{{ route('admin.monitoring') }}" method="GET" class="mb-6 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
            <input type="hidden" name="tab" value="pekerjaan">

            {{-- Kiri: Input Cari + Tombol Cari --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Cari order, customer..." 
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <button type="submit" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl transition shrink-0">
                    Cari
                </button>
            </div>

            {{-- Kanan: Filter Status + Urutkan --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto justify-end">
                <select name="status_order" onchange="this.form.submit()" class="w-full sm:w-auto text-xs py-2 px-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600">
                    <option value="">Semua Status Order</option>
                    <option value="pending" {{ request('status_order') === 'pending' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="waiting_designer" {{ request('status_order') === 'waiting_designer' ? 'selected' : '' }}>Mencari Kreator</option>
                    <option value="in_progress" {{ request('status_order') === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                    <option value="revision_needed" {{ request('status_order') === 'revision_needed' ? 'selected' : '' }}>Revisi Diperlukan</option>
                    <option value="deliverable_sent" {{ request('status_order') === 'deliverable_sent' ? 'selected' : '' }}>Hasil Dikirim</option>
                    <option value="completed" {{ request('status_order') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status_order') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <select name="sort" onchange="this.form.submit()" class="w-full sm:w-auto text-xs py-2 px-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600">
                    <option value="terbaru" {{ request('sort') === 'terbaru' || !request('sort') ? 'selected' : '' }}>Terbaru</option>
                    <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                    <option value="deadline" {{ request('sort') === 'deadline' ? 'selected' : '' }}>Mendekati Deadline</option>
                </select>
            </div>
        </form>

        {{-- Tabel Pekerjaan --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-700 font-semibold uppercase text-[11px] border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Kode & Produk</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Desainer</th>
                            <th class="px-6 py-4">Total Harga</th>
                            <th class="px-6 py-4">Tenggat Waktu</th>
                            <th class="px-6 py-4">Status Pengerjaan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $order->order_code }}</div>
                                    <div class="text-gray-500 mt-0.5">
                                        {{ $order->product->name ?? 'Produk' }} 
                                        @if($order->productTier)
                                            <span class="text-[10px] text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded font-medium">
                                                {{ $order->productTier->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $order->customer->full_name ?? '-' }}</div>
                                    <div class="text-gray-400 text-[11px]">{{ $order->customer->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->designer)
                                        <div class="font-medium text-gray-900">{{ $order->designer->full_name }}</div>
                                        <div class="text-[10px] text-emerald-600">Telah ditugaskan</div>
                                    @else
                                        <span class="inline-flex items-center text-amber-600 bg-amber-50 px-2 py-0.5 rounded text-[10px] font-medium">
                                            Belum Ada Desainer
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->deadline)
                                        <div class="{{ $order->isDeadlineMissed() ? 'text-rose-600 font-bold' : 'text-gray-700' }}">
                                            {{ $order->deadline->translatedFormat('d M Y, H:i') }}
                                        </div>
                                        @if($order->isDeadlineMissed())
                                            <span class="text-[10px] text-rose-500 font-semibold">(Lewat Deadline)</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
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
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium border {{ $statusClass }}">
                                        {{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" 
                                            @click="fetchDetail('{{ route('admin.monitoring.detail', $order->id) }}')" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-xl transition shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-gray-400">
                                    Tidak ada data pesanan/pekerjaan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== TAB 2: VERIFIKASI PEMBAYARAN ==================== --}}
    <div x-show="tab === 'pembayaran'" x-cloak>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-2xl border border-gray-100 shadow-sm text-xs">
                <button type="button"
                        @click="subtab = 'pending'" 
                        :class="subtab === 'pending' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'"
                        class="px-3.5 py-1.5 rounded-xl font-medium transition">
                    Menunggu Verifikasi
                </button>

                <button type="button"
                        @click="subtab = 'berhasil'" 
                        :class="subtab === 'berhasil' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'"
                        class="px-3.5 py-1.5 rounded-xl font-medium transition">
                    Berhasil / ACC
                </button>

                <button type="button"
                        @click="subtab = 'gagal'" 
                        :class="subtab === 'gagal' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'"
                        class="px-3.5 py-1.5 rounded-xl font-medium transition">
                    Ditolak
                </button>

                <button type="button"
                        @click="subtab = 'semua'" 
                        :class="subtab === 'semua' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'"
                        class="px-3.5 py-1.5 rounded-xl font-medium transition">
                    Semua Riwayat
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-700 font-semibold uppercase text-[11px] border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Kode Order</th>
                            <th class="px-6 py-4">Pembeli</th>
                            <th class="px-6 py-4">Tipe & Metode</th>
                            <th class="px-6 py-4">Nominal</th>
                            <th class="px-6 py-4">Bukti Transfer</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payments as $payment)
                            <tr x-show="subtab === 'semua' || subtab === '{{ $payment->status }}'" 
                                x-cloak
                                class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    {{ $payment->order->order_code ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $payment->order->customer->full_name ?? 'Customer' }}</div>
                                    <div class="text-gray-400 text-[11px]">{{ $payment->order->customer->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="capitalize font-medium text-gray-800">
                                        {{ str_replace('_', ' ', $payment->type) }}
                                    </span>
                                    <div class="text-gray-400 text-[11px] uppercase mt-0.5">{{ $payment->method }}</div>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($payment->proof_url_temp)
                                        <button type="button" 
                                                @click="openModal = true; 
                                                        activeImage = '{{ $payment->proof_url_temp }}'; 
                                                        activeOrderCode = '{{ $payment->order->order_code ?? '' }}';
                                                        formApproveUrl = '{{ route('admin.monitoring.pembayaran.approve', $payment->id) }}';
                                                        formRejectUrl = '{{ route('admin.monitoring.pembayaran.reject', $payment->id) }}';"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-600 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl transition">
                                            Lihat Foto
                                        </button>
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada foto</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($payment->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu ACC
                                        </span>
                                    @elseif($payment->status === 'berhasil')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Berhasil
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($payment->isPending())
                                        <div class="flex items-center justify-center gap-2">
                                            <form action="{{ route('admin.monitoring.pembayaran.approve', $payment->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        onclick="return confirm('Apakah Anda yakin ingin ACC pembayaran ini?')" 
                                                        class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm">
                                                    ACC
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.monitoring.pembayaran.reject', $payment->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        onclick="return confirm('Apakah Anda yakin ingin menolak pembayaran ini?')" 
                                                        class="px-3 py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition shadow-sm">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-[11px]">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-gray-400">
                                    Tidak ada data pembayaran yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW BUKTI TRANSFER --}}
    <template x-teleport="body">
        <div x-show="openModal" 
             x-cloak
             class="fixed inset-0 z-[9999] overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative" @click.away="openModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Foto Bukti Transfer</h3>
                        <p class="text-xs text-gray-400">Order Code: <span class="font-semibold text-purple-600" x-text="activeOrderCode"></span></p>
                    </div>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex justify-center bg-gray-900/5 rounded-2xl p-2 border border-gray-100 max-h-[60vh] overflow-auto">
                    <img :src="activeImage" alt="Bukti Transfer" class="max-w-full h-auto rounded-xl object-contain shadow-sm">
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button @click="openModal = false" type="button" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                        Tutup
                    </button>

                    <template x-if="formRejectUrl">
                        <form :action="formRejectUrl" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" onclick="return confirm('Tolak pembayaran ini?')" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition shadow-sm">
                                Tolak
                            </button>
                        </form>
                    </template>

                    <template x-if="formApproveUrl">
                        <form :action="formApproveUrl" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" onclick="return confirm('ACC pembayaran ini?')" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm">
                                ACC Pembayaran
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL DETAIL MONITORING PENGERJAAN (SESUAI WIREFRAME DESIGN) --}}
    <template x-teleport="body">
        <div x-show="detailModalOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6">

            <div @click.away="detailModalOpen = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-6">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Detail Monitoring Pengerjaan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Informasi lengkap status dan rekam jejak progres pesanan.</p>
                    </div>
                    <button @click="detailModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Loading Indicator --}}
                <template x-if="detailLoading">
                    <div class="flex items-center justify-center gap-2 py-12 text-xs text-slate-400 font-medium">
                        <svg class="w-5 h-5 animate-spin text-purple-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memuat detail pesanan...
                    </div>
                </template>

                {{-- Modal Body Data --}}
                <template x-if="!detailLoading && detailData">
                    <div class="space-y-6 max-h-[75vh] overflow-y-auto pr-1 no-scrollbar">
                        
                        {{-- Section 1: Informasi Pesanan --}}
                        <div class="p-5 bg-slate-50/80 rounded-2xl border border-slate-100 space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200/60 pb-2">Informasi Pesanan</h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400">Nama Jasa / Paket</p>
                                    <p class="font-bold text-slate-800 mt-0.5" x-text="detailData.product_name"></p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400">Nomor Pesanan</p>
                                    <p class="font-bold text-purple-600 mt-0.5" x-text="detailData.order_code"></p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400">Nama Customer (Klien)</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-[10px]" x-text="detailData.customer_name.charAt(0).toUpperCase()"></div>
                                        <span class="font-semibold text-slate-800" x-text="detailData.customer_name"></span>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400">Nama Designer (Kreator)</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px]" x-text="detailData.designer_name.charAt(0).toUpperCase()"></div>
                                        <span class="font-semibold text-slate-800" x-text="detailData.designer_name"></span>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400">Batas Waktu Pengerjaan (Deadline)</p>
                                    <p class="font-semibold mt-0.5" :class="detailData.is_late ? 'text-rose-600 font-bold' : 'text-slate-800'" x-text="detailData.deadline"></p>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400">Terakhir Diperbarui</p>
                                    <p class="font-semibold text-slate-800 mt-0.5" x-text="detailData.updated_at"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Timeline Progress --}}
                        <div class="p-5 bg-white rounded-2xl border border-slate-100 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Timeline Progress</h4>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200" x-text="detailData.status_label"></span>
                            </div>

                            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                                <template x-for="(log, idx) in detailData.logs" :key="log.id || idx">
                                    <div class="relative flex items-start justify-between gap-4">
                                        
                                        {{-- Node Marker Icon --}}
                                        <span class="absolute -left-6 top-0.5 w-5 h-5 rounded-full flex items-center justify-center border-2"
                                              :class="idx === detailData.logs.length - 1 ? 'bg-purple-600 border-purple-600 text-white shadow-sm ring-4 ring-purple-100' : 'bg-slate-900 border-slate-900 text-white'">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </span>

                                        {{-- Content Info --}}
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="text-xs font-bold text-slate-900" x-text="log.action.replace(/_/g, ' ').toUpperCase()"></p>
                                                <template x-if="idx === detailData.logs.length - 1">
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-purple-100 text-purple-700 uppercase">Tahap Aktif</span>
                                                </template>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-1" x-text="log.note"></p>
                                            <p class="text-[10px] text-slate-400 mt-1">Oleh: <span class="font-medium text-slate-500" x-text="log.actor_name"></span></p>
                                        </div>

                                        {{-- Timestamp --}}
                                        <span class="text-[11px] font-semibold text-slate-400 shrink-0 whitespace-nowrap" x-text="log.time"></span>
                                    </div>
                                </template>

                                <template x-if="!detailData.logs || detailData.logs.length === 0">
                                    <p class="text-xs text-slate-400 italic">Belum ada catatan aktivitas untuk pesanan ini.</p>
                                </template>
                            </div>
                        </div>

                    </div>
                </template>

            </div>
        </div>
    </template>

</div>
@endsection