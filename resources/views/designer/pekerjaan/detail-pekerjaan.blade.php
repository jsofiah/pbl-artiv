@extends('layouts.designer')

@section('title', 'Detail Pekerjaan')

@section('content')
<div>
    <a href="{{ route('designer.pekerjaan.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-[#6D28D9] mb-4 transition rounded-full px-3 py-2 bg-white border border-slate-200 hover:bg-[#D5FC55] hover:text-[#1A1A1A]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke Pekerjaan Saya
    </a>

    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-slate-900">Detail Pekerjaan</h1>
        <p class="text-slate-500 mt-1">
            {{ $order->order_code }} · Kelola pengerjaan dan komunikasi dengan customer.
        </p>
    </div>

    @include('designer.pekerjaan.partials.info-cards')
    @include('designer.pekerjaan.partials.chat')
</div>

@include('designer.pekerjaan.partials.modals')
@endsection

@push('scripts')
    @include('designer.pekerjaan.partials.scripts')
@endpush