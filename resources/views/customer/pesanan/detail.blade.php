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
@endsection

@push('scripts')
    @include('customer.pesanan.scripts', ['order' => $order])
@endpush