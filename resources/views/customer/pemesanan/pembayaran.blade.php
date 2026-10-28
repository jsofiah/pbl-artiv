@extends('layouts.customer')

@section('title', 'Pembayaran Pesanan')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full" x-data="paymentForm()">

        <!-- Header / Breadcrumb -->
        <div class="mb-6">
            <span class="text-xs font-bold tracking-wider text-[#745BB8] uppercase">
                Tahap 3 dari 3 • Pembayaran Pesanan
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 mt-1">
                Konfirmasi Pembayaran
            </h1>
            <p class="text-sm text-neutral-600 mt-1">
                Pastikan pesanan anda sudah sesuai dan lakukan pembayaran.
            </p>
        </div>

        {{-- <!-- Error / Success Alert -->
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif --}}

        <!-- Form Pembayaran -->
        <form action="{{ route('customer.pemesanan.pembayaran.store', $order->id) }}" method="POST"
            enctype="multipart/form-data" class="space-y-6" @submit="isSubmitting = true">
            @csrf

            <!-- Hidden inputs untuk data pembayaran pendukung -->
            <input type="hidden" name="method" value="QRIS">
            <input type="hidden" name="amount" value="{{ $order->total_price }}">
            <input type="hidden" name="type" value="order">

            <!-- Card 1: Informasi Pembayaran (QRIS) -->
            <div class="bg-white rounded-3xl shadow-sm border border-neutral-200/80 p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-8 h-8 rounded-full bg-neutral-900 text-white flex items-center justify-center font-bold text-sm">
                        1
                    </div>
                    <h2 class="text-lg font-bold text-neutral-900">Informasi Pembayaran</h2>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center bg-neutral-50/50 border border-neutral-100 rounded-2xl p-6">
                    <!-- QR Code Section -->
                    <div class="md:col-span-5 flex justify-center">
                        <div
                            class="bg-white p-3 rounded-2xl shadow-sm border border-neutral-200 w-48 h-48 flex items-center justify-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ARTIV-PAYMENT-{{ $order->order_code }}"
                                alt="QRIS Code" class="w-full h-full object-contain">
                        </div>
                    </div>

                    <!-- Detail Nominal & Timer -->
                    <div class="md:col-span-7 space-y-4">
                        <div>
                            <span class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Total
                                Nominal:</span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-[#745BB8] mt-0.5">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Merchant
                                Resmi:</span>
                            <div class="text-sm font-semibold text-neutral-900 mt-0.5">
                                ARTIV Official
                            </div>
                        </div>

                        <!-- Batas Waktu Bayar -->
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-center justify-between"
                            x-data="paymentCountdown('{{ $order->created_at->addDay()->toIso8601String() }}')" x-init="init()">
                            <div>
                                <p class="text-xs font-bold text-amber-800 uppercase tracking-wide">Batas Waktu Pembayaran
                                </p>
                                <p class="text-sm text-amber-600">Selesaikan pembayaran sebelum waktu habis.</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-extrabold text-amber-900" x-text="timeLeft">Memuat...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Langkah Pembayaran QRIS -->
                <div class="mt-6 pt-6 border-t border-neutral-100">
                    <h3 class="text-xs font-bold text-neutral-700 uppercase tracking-wider mb-3">Langkah Pembayaran QRIS:
                    </h3>
                    <ol class="list-decimal list-inside space-y-1.5 text-xs sm:text-sm text-neutral-600">
                        <li>Pastikan paket, harga, dan total pembayaran sudah sesuai.</li>
                        <li>Pilih menu Scan / Bayar dan arahkan kamera ke kode QR di samping.</li>
                        <li>Pastikan nama merchant tertera <span class="font-semibold text-neutral-800">ARTIV
                                Official</span>.</li>
                        <li>Ikuti instruksi pembayaran hingga transaksi berhasil diproses.</li>
                        <li>Setelah pembayaran berhasil dikonfirmasi, pesanan akan masuk ke sistem dan tunggu desainer
                            mengambil order.</li>
                    </ol>
                </div>
            </div>

            <!-- Card 2: Upload Bukti Pembayaran (Format disamakan seperti form pemesanan) -->
            <div class="bg-white rounded-3xl shadow-sm border border-neutral-200/80 p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-8 h-8 rounded-full bg-neutral-900 text-white flex items-center justify-center font-bold text-sm">
                        2
                    </div>
                    <h2 class="text-lg font-bold text-neutral-900">Upload Bukti Pembayaran</h2>
                </div>

                <div class="space-y-4">
                    <!-- Dropzone Area -->
                    <div class="relative border-2 border-dashed rounded-2xl p-6 text-center transition bg-neutral-50/50 hover:bg-neutral-50 group cursor-pointer"
                        :class="fileName ? 'border-[#745BB8] bg-[#745BB8]/5' : 'border-neutral-300 hover:border-[#745BB8]'">
                        
                        <input type="file" name="proof" id="proof" accept="image/jpeg,image/png,image/jpg"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handleFileChange"
                            required>

                        <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                            <div class="w-10 h-10 rounded-full bg-[#745BB8]/10 text-[#745BB8] flex items-center justify-center group-hover:scale-110 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-neutral-800"
                                    x-text="fileName ? 'File Dipilih: ' + fileName : 'Unggah Struk atau Bukti Transfer'"></p>
                                <p class="text-xs text-neutral-500 mt-0.5" x-show="!fileName">
                                    Seret & letakkan file di sini, atau <span class="text-[#745BB8] font-medium">pilih file</span> (JPG, PNG, Maks. 2MB)
                                </p>
                                <p class="text-xs text-[#745BB8] font-medium mt-0.5" x-show="fileName">
                                    Klik atau seret file lain untuk mengganti
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- File Preview Card (Muncul saat file dipilih) -->
                    <div class="flex items-center justify-between p-3.5 bg-white border border-neutral-200 rounded-xl shadow-xs" x-show="fileName" style="display: none;">
                        <div class="flex items-center space-x-3 overflow-hidden">
                            <div class="w-9 h-9 rounded-lg bg-[#745BB8]/10 text-[#745BB8] flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="text-xs truncate">
                                <p class="font-semibold text-neutral-900 truncate" x-text="fileName"></p>
                                <p class="text-neutral-500 mt-0.5" x-text="fileSize"></p>
                            </div>
                        </div>
                        <button type="button" @click="removeFile" class="text-neutral-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Verifikasi Cepat Info -->
                <div
                    class="mt-4 bg-[#745BB8]/5 border border-[#745BB8]/15 rounded-xl p-3.5 flex items-center gap-3 text-xs text-[#745BB8] font-medium">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Verifikasi Cepat: Notifikasi konfirmasi akan langsung dikirim ke menu Pesanan Saya.</span>
                </div>
            </div>

            <!-- Alur Langkah Selanjutnya -->
            <div class="bg-white rounded-3xl shadow-sm border border-neutral-200/80 p-6">
                <h3 class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-4">Alur Langkah Selanjutnya:</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-sm text-neutral-700">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                        <span class="font-medium">Pengisian Formulir</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-neutral-700">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                        <span class="font-medium">Konfirmasi Ringkasan</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm font-semibold text-[#745BB8] bg-[#745BB8]/5 p-2.5 rounded-xl border border-[#745BB8]/20">
                        <span class="w-5 h-5 rounded-full bg-[#745BB8] text-white flex items-center justify-center text-xs font-bold">3</span>
                        <span>Pembayaran Pesanan (Aktif)</span>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-8 py-3.5 bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-950 font-bold text-sm rounded-2xl shadow-sm transition flex items-center gap-2 disabled:opacity-50"
                    :disabled="isSubmitting">
                    <span x-show="!isSubmitting">Kirim</span>
                    <span x-show="isSubmitting">Mengirim...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function paymentCountdown(expiryDateStr) {
                return {
                    timeLeft: '',
                    init() {
                        const expiryTime = new Date(expiryDateStr).getTime();
                        const updateTimer = () => {
                            const now = new Date().getTime();
                            const distance = expiryTime - now;
                            if (distance < 0) {
                                this.timeLeft = 'Waktu Habis!';
                                return;
                            }
                            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                            this.timeLeft = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                        };
                        updateTimer();
                        setInterval(updateTimer, 1000);
                    }
                }
            }

            function paymentForm() {
                return {
                    fileName: '',
                    fileSize: '',
                    isSubmitting: false,
                    handleFileChange(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.fileName = file.name;
                            this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                        } else {
                            this.fileName = '';
                            this.fileSize = '';
                        }
                    },
                    removeFile() {
                        this.fileName = '';
                        this.fileSize = '';
                        document.getElementById('proof').value = '';
                    }
                }
            }
        </script>
    @endpush
@endsection