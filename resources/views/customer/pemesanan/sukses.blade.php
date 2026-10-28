@extends('layouts.customer')

@section('title', 'Menunggu Verifikasi Pembayaran')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

        <!-- Header / Breadcrumb -->
        <div class="mb-6 text-center sm:text-left">
            <span class="text-xs font-bold tracking-wider text-[#745BB8] uppercase">
                Tahap Selesai • Verifikasi Pembayaran
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 mt-1">
                Pembayaran Sedang Diproses
            </h1>
            <p class="text-sm text-neutral-600 mt-1">
                Bukti transfer Anda telah diterima dan sedang dalam pengecekan oleh tim admin kami.
            </p>
        </div>

        <!-- Card Utama Status Pembayaran -->
        <div class="bg-white rounded-3xl shadow-sm border border-neutral-200/80 p-6 sm:p-8 space-y-6">
            
            <!-- Ilustrasi / Badge Status Pending -->
            <div class="flex flex-col items-center justify-center text-center py-6 bg-amber-50/60 border border-amber-200/60 rounded-2xl p-6">
                <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-3 shadow-inner">
                    <svg class="w-8 h-8 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-amber-900">Menunggu Verifikasi Admin</h2>
                <p class="text-xs sm:text-sm text-amber-700 max-w-md mt-1">
                    Biasanya proses verifikasi memakan waktu 10 hingga 30 menit pada jam operasional. Status pesanan Anda akan diperbarui otomatis setelah dikonfirmasi.
                </p>
            </div>

            <!-- Detail Ringkasan Pesanan & Pembayaran -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <!-- Info Pesanan -->
                <div class="bg-neutral-50/50 border border-neutral-100 rounded-2xl p-5 space-y-3">
                    <h3 class="text-xs font-bold text-neutral-500 uppercase tracking-wider">Detail Pesanan</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Kode Pesanan:</span>
                            <span class="font-semibold text-neutral-900">{{ $order->order_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Total Tagihan:</span>
                            <span class="font-bold text-[#745BB8]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Status Order:</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 uppercase">
                                {{ $order->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info Pembayaran yang Diunggah -->
                <div class="bg-neutral-50/50 border border-neutral-100 rounded-2xl p-5 space-y-3">
                    <h3 class="text-xs font-bold text-neutral-500 uppercase tracking-wider">Informasi Transfer</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Metode:</span>
                            <span class="font-semibold text-neutral-900">{{ $payment?->method ?? 'QRIS' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Waktu Unggah:</span>
                            <span class="font-medium text-neutral-900">{{ $payment?->created_at?->format('d M Y, H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Status Pembayaran:</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 uppercase">
                                {{ $payment?->status ?? 'Pending' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Bantuan / Catatan -->
            <div class="bg-[#745BB8]/5 border border-[#745BB8]/15 rounded-xl p-4 flex items-center gap-3 text-xs sm:text-sm text-[#745BB8] font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Anda dapat memantau progres pesanan secara berkala melalui menu Pesanan Saya.</span>
            </div>

            <!-- Tombol Aksi Navigasi -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-neutral-100">
                <a href="{{ route('customer.pesanan') }}" 
                   class="w-full sm:w-auto px-8 py-3.5 bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-950 font-bold text-sm rounded-2xl shadow-sm transition text-center flex items-center justify-center gap-2">
                    <span>Menu Pesanan Saya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>

        </div>
    </div>
@endsection