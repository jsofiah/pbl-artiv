@extends('layouts.admin')

@section('title', 'Laporan Transaksi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ openCustomModal: false }">

    {{-- Daftar Kartu Laporan Per Tahun --}}
    @foreach ($monthsByYear as $year => $months)
        <div class="space-y-3">
            
            {{-- Header Tahun & Tombol Atur Periode (Sejajar) --}}
            <div class="flex items-center justify-between px-1">
                <h2 class="text-sm font-bold text-black tracking-wide">
                    {{ $year }}
                </h2>

                @if ($loop->first)
                    <button @click="openCustomModal = true" 
                            type="button" 
                            class="inline-flex items-center gap-2 px-4 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-full shadow-sm transition">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Atur Periode
                    </button>
                @endif
            </div>

            <div class="space-y-2">
                @foreach ($months as $m)
                    <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center justify-between hover:shadow-sm transition-all">
                        
                        {{-- Nama Bulan --}}
                        <div>
                            <h3 class="text-base font-bold text-gray-800">
                                {{ $m['name'] }}
                            </h3>
                        </div>

                        {{-- Tombol Unduh Format Teks Biru Simpel --}}
                        <form action="{{ route('admin.laporan.export') }}" method="POST" class="flex items-center gap-3">
                            @csrf
                            <input type="hidden" name="start_date" value="{{ $m['start_date'] }}">
                            <input type="hidden" name="end_date" value="{{ $m['end_date'] }}">

                            {{-- Excel --}}
                            <button type="submit" name="format" value="xlsx" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline transition">
                                Excel
                            </button>

                            <span class="text-gray-300">|</span>

                            {{-- PDF --}}
                            <button type="submit" name="format" value="pdf" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline transition">
                                PDF
                            </button>

                            <span class="text-gray-300">|</span>

                            {{-- CSV --}}
                            <button type="submit" name="format" value="csv" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline transition">
                                CSV
                            </button>
                        </form>

                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- MODAL ATUR PERIODE KUSTOM --}}
    <div x-show="openCustomModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/40 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="openCustomModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-bold text-gray-800">Atur Rentang Tanggal</h3>
                <button @click="openCustomModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.laporan.export') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button type="submit" name="format" value="xlsx" @click="openCustomModal = false" class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline">
                        Excel
                    </button>
                    <span class="text-gray-300">|</span>
                    <button type="submit" name="format" value="pdf" @click="openCustomModal = false" class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline">
                        PDF
                    </button>
                    <span class="text-gray-300">|</span>
                    <button type="submit" name="format" value="csv" @click="openCustomModal = false" class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline">
                        CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection