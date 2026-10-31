@extends('layouts.customer')

@section('title', 'Detail Pesanan')

@section('content')
<div class="max-w-[1280px] mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-slate-900">Detail Pesanan</h1>
        <div class="w-14 h-1.5 bg-[#6D28D9] rounded-full mt-2 mb-4"></div>
        <p class="text-slate-500 max-w-2xl">
            Pantau perkembangan pengerjaan proyek dan komunikasi langsung dengan desainer.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[420px_1fr] gap-6 items-start">
        @include('customer.pesanan.partials.sidebar', ['order' => $order])
        @include('customer.pesanan.partials.chat', ['order' => $order])
    </div>
</div>

@include('customer.pesanan.partials.modals', ['order' => $order])

@if ($order->canCustomerRefund())
<div class="p-4 bg-rose-50 border border-rose-200 rounded-xl mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h4 class="font-bold text-rose-700 text-sm mb-1 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Designer Sebelumnya Berhalangan
        </h4>
        <p class="text-sm text-rose-600 leading-relaxed">
            Sistem sedang mencarikan designer baru dengan prioritas <strong>Urgent</strong>. Jika kamu tidak ingin menunggu lebih lama, kamu memiliki hak untuk mengajukan pengembalian dana.
        </p>
    </div>
    <button type="button" onclick="document.getElementById('modal-refund').classList.remove('hidden')"
            class="whitespace-nowrap px-4 py-2 bg-rose-600 text-white text-sm font-semibold rounded-xl hover:bg-rose-700 transition shadow-sm">
        Ajukan Refund
    </button>
</div>

<div id="modal-refund" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative">
        <h3 class="text-xl font-bold text-slate-900 mb-2">Ajukan Pengembalian Dana</h3>
        <p class="text-sm text-slate-500 mb-5">Silakan isi data rekening tujuan. Admin akan memproses pengembalian dana dalam 1-3 hari kerja.</p>

        <form method="POST" action="{{ route('customer.pesanan.refund', $order->id) }}">
            @csrf
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Bank</label>
                    <input type="text" name="bank_name" placeholder="Contoh: BCA, Mandiri, BNI" required 
                           class="w-full text-sm border border-slate-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-rose-400/50 focus:border-rose-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Rekening</label>
                    <input type="number" name="bank_account_number" placeholder="Masukkan nomor rekening" required 
                           class="w-full text-sm border border-slate-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-rose-400/50 focus:border-rose-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Pemilik Rekening</label>
                    <input type="text" name="bank_account_name" placeholder="Sesuai buku tabungan" required 
                           class="w-full text-sm border border-slate-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-rose-400/50 focus:border-rose-400 transition-all">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modal-refund').classList.add('hidden')" 
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors">
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
    @include('customer.pesanan.scripts', ['order' => $order])
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnContinue = document.getElementById('btn-continue-reassign');
        const formContinue = document.getElementById('form-continue-reassign');

        if (btnContinue && formContinue) {
            btnContinue.addEventListener('click', function () {
                window.appConfirm(
                    'Dengan melanjutkan, Anda setuju bahwa deadline pengerjaan akan mundur 3 hari dari jadwal semula. Order akan dialihkan ke designer pengganti dengan prioritas Urgent.',
                    () => {
                        formContinue.submit();
                    },
                    {
                        title: 'Dikerjakan Designer Lain?',
                        okText: 'Ya, Setuju & Lanjutkan',
                        cancelText: 'Batal',
                        variant: 'primary',
                    }
                );
            });
        }
    });
    </script>
@endpush