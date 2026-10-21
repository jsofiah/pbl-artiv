@extends('layouts.designer')
@section('title', 'Dashboard')

@php
    $rp = fn (float|int $n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

@section('content')
    <div class="flex items-start justify-between gap-4 flex-wrap mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">
                Halo, {{ auth()->user()?->full_name ?? 'Designer' }}!
            </h1>
            <p class="text-slate-500 mt-1.5">Ringkasan pekerjaan, pendapatan, dan aktivitas terbarumu hari ini.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-300 bg-white text-sm font-medium text-slate-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path stroke-linecap="round" d="M8 2v4M16 2v4M3 9h18"/></svg>
                {{ now()->format('d/m/Y') }}
            </span>

            @php
                $statusLabel = $isSuspended
                    ? 'Suspended'
                    : ($isAvailable ? 'Available' : 'Unavailable');

                $statusClass = $isSuspended
                    ? 'border-red-200 bg-red-50 text-red-600'
                    : ($isAvailable
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                        : 'border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100');

                $dotClass = $isSuspended
                    ? 'bg-red-500'
                    : ($isAvailable ? 'bg-emerald-500' : 'bg-slate-400');
            @endphp

            <form method="POST" action="{{ route('designer.availability.toggle') }}" class="inline">
                @csrf
                <button type="submit"
                        @if ($isSuspended) disabled @endif
                        title="{{ $isSuspended ? 'Tidak bisa ubah status saat suspended' : 'Klik untuk toggle status' }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border-2 text-sm font-semibold transition {{ $statusClass }}
                            {{ $isSuspended ? 'cursor-not-allowed opacity-75' : 'cursor-pointer' }}">
                    <span class="w-2 h-2 rounded-full {{ $dotClass }} animate-pulse"></span>
                    {{ $statusLabel }}
                </button>
            </form>

            <a href="{{ route('designer.job-pool') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#6D28D9] bg-white text-sm font-semibold text-[#6D28D9] hover:bg-[#6D28D9] hover:text-white transition">
                Jelajahi Job Pool
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    </div>

    @php
        $kartu = [
            ['label' => 'Order Aktif', 'nilai' => $statistik['order_aktif'], 'format' => 'int', 'ket' => 'Sedang dikerjakan', 'icon' => 'folder', 'accent' => 'violet'],
            ['label' => 'Order Selesai', 'nilai' => $statistik['order_selesai'], 'format' => 'int', 'ket' => 'Total pekerjaan tuntas', 'icon' => 'check', 'accent' => 'lime'],
            ['label' => 'Rating Rata-rata', 'nilai' => $statistik['rating'], 'format' => 'rating', 'ket' => 'Dari ' . $statistik['jumlah_ulasan'] . ' pesanan', 'icon' => 'star', 'accent' => 'amber'],
            ['label' => 'Pendapatan', 'nilai' => $statistik['total_pendapatan'], 'format' => 'currency', 'ket' => 'Total keseluruhan', 'icon' => 'wallet', 'accent' => 'violet'],
        ];
    @endphp

    @if ($isSuspended || ($stats?->late_count > 0) || ($stats?->suspend_count > 0))
        <div class="mb-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-amber-800">Perhatian</p>
                <ul class="text-sm text-amber-700 mt-1.5 list-disc list-inside space-y-1">
                    @if ($isSuspended)
                        <li class="text-red-700 font-semibold">
                            Anda di-suspend sampai
                            <strong>{{ $stats->suspended_until->translatedFormat('d M Y, H:i') }} WIB</strong>.
                            Tidak bisa ambil order baru.
                        </li>
                    @endif

                    @if ($stats?->late_count > 0)
                        <li>
                            Anda terlambat <strong>{{ $stats->late_count }}</strong> kali.
                            Batas sebelum suspend: <strong>{{ \App\Models\DesignerStat::LATE_THRESHOLD_SUSPEND }}x</strong>.
                        </li>
                    @endif

                    @if ($stats?->suspend_count > 0)
                        <li>
                            Anda pernah di-suspend <strong>{{ $stats->suspend_count }}</strong> kali.
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        @foreach ($kartu as $k)
            @php
                $iconBg = match($k['accent']) {
                    'violet' => 'bg-violet-50 text-[#6D28D9] border-violet-200',
                    'lime'   => 'bg-lime-50 text-lime-700 border-lime-200',
                    'amber'  => 'bg-amber-50 text-amber-600 border-amber-200',
                    default  => 'bg-slate-50 text-slate-500 border-slate-200',
                };
            @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <p class="text-sm text-slate-500">{{ $k['label'] }}</p>
                    <span class="w-9 h-9 rounded-xl border flex items-center justify-center {{ $iconBg }}">
                        @switch($k['icon'])
                            @case('folder')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                                @break
                            @case('check')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 12l3 3 5-6"/></svg>
                                @break
                            @case('star')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3z"/></svg>
                                @break
                            @case('wallet')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="14" rx="2"/><path d="M16 13h2M3 10h18"/></svg>
                        @endswitch
                    </span>
                </div>

                <p class="text-3xl font-extrabold text-slate-900 mt-6 tracking-tight"
                data-animate-value="{{ $k['nilai'] }}"
                data-format="{{ $k['format'] }}">
                    @switch($k['format'])
                        @case('currency')
                            {{ $rp($k['nilai']) }}
                            @break
                        @case('rating')
                            {{ number_format($k['nilai'], 1) }} / 5.0
                            @break
                        @default
                            {{ sprintf('%02d', $k['nilai']) }}
                    @endswitch
                </p>

                <p class="text-xs text-slate-500 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>{{ $k['ket'] }}
                </p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-6 items-start">
        <div class="space-y-6 min-w-0">
            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-900">Kapasitas Order</h3>
                    <span class="text-xs font-bold
                                @if ($persenKapasitas >= 100) text-red-600
                                @elseif ($persenKapasitas >= 80) text-amber-600
                                @else text-emerald-600
                                @endif">
                        {{ $kapasitasTerpakai }} / {{ $kapasitasMax }}
                    </span>
                </div>

                <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000 ease-out
                                @if ($persenKapasitas >= 100) bg-red-500
                                @elseif ($persenKapasitas >= 80) bg-amber-500
                                @else bg-emerald-500
                                @endif"
                        style="width: {{ $persenKapasitas }}%"></div>
                </div>

                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    @if ($persenKapasitas >= 100)
                        <span class="text-red-600 font-semibold">
                            Penuh — tidak bisa ambil order baru sampai ada yang selesai.
                        </span>
                    @elseif ($persenKapasitas >= 80)
                        <span class="text-amber-600 font-semibold">
                            Hampir penuh. Selesaikan order dulu sebelum ambil yang baru.
                        </span>
                    @else
                        Masih bisa ambil
                        <strong class="text-slate-800">{{ $kapasitasMax - $kapasitasTerpakai }} order</strong>
                        lagi.
                    @endif
                </p>
            </section>

            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Status Order Aktif</h2>
                    <span class="text-xs text-slate-500">{{ $orderAktif->count() }} total</span>
                </div>

                @if ($orderAktif->count() > 0)
                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        <div class="relative w-40 h-40 shrink-0">
                            <canvas id="statusChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <p class="text-2xl font-extrabold text-slate-900">{{ $orderAktif->count() }}</p>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wide">Total</p>
                            </div>
                        </div>
                        <div class="flex-1 space-y-2 w-full">
                            @php
                                $statusItems = [
                                    ['label' => 'Dikerjakan', 'count' => $statusBreakdown['in_progress'], 'color' => '#6D28D9'],
                                    ['label' => 'Menunggu Review', 'count' => $statusBreakdown['deliverable_sent'], 'color' => '#A78BFA'],
                                    ['label' => 'Revisi', 'count' => $statusBreakdown['revision_needed'], 'color' => '#D5FC55'],
                                    ['label' => 'Menunggu Keputusan', 'count' => $statusBreakdown['waiting_decision'], 'color' => '#FBC003'],
                                ];
                            @endphp
                            @foreach ($statusItems as $item)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-2 text-slate-600">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                        {{ $item['label'] }}
                                    </span>
                                    <span class="font-semibold text-slate-900">{{ $item['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-400 py-8 text-center">Belum ada order aktif.</p>
                @endif
            </section>

            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <h2 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                        Order Aktif
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-violet-50 text-[#6D28D9]">{{ $orderAktif->count() }}</span>
                    </h2>
                    <a href="{{ route('designer.pekerjaan.index') }}" class="text-xs font-medium text-slate-500 hover:text-[#6D28D9]">Lihat Semua &rsaquo;</a>
                </div>

                <div class="space-y-3">
                    @forelse ($orderAktif as $order)
                        @php $progress = $order->progress; @endphp
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 flex items-start justify-between gap-4 hover:border-[#6D28D9]/30 transition">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full border border-slate-300 bg-white text-slate-600">
                                        {{ $order->order_code }}
                                    </span>
                                    <span class="text-xs text-slate-500">Biaya: {{ $rp($order->total_price) }}</span>
                                </div>
                                <h3 class="font-semibold text-slate-900 mt-2 truncate">
                                    {{ $order->product?->name ?? 'Layanan' }}
                                </h3>
                                <div class="mt-3 flex items-center gap-2 max-w-xs">
                                    <div class="flex-1 h-1.5 rounded-full bg-slate-200 overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-[#6D28D9] to-[#D5FC55] transition-all duration-1000"
                                            style="width: {{ $progress['percent'] }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-500">{{ $progress['percent'] }}%</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end justify-between self-stretch shrink-0">
                                <span class="text-xs text-slate-500 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                    {{ $order->deadline?->translatedFormat('d M') ?? '-' }}
                                </span>
                                <a href="{{ route('designer.pekerjaan.show', $order->id) }}"
                                class="mt-3 text-xs font-semibold px-4 py-1.5 rounded-full border border-slate-300 bg-white text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9] transition">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 py-6 text-center">Belum ada order aktif.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <h2 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                        Pekerjaan Terbaru
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-violet-50 text-[#6D28D9]">{{ $pekerjaanTerbaru->count() }} baru</span>
                    </h2>
                    <a href="{{ route('designer.job-pool') }}" class="text-xs font-medium text-slate-500 hover:text-[#6D28D9]">Lihat Semua &rsaquo;</a>
                </div>

                <div class="space-y-3">
                    @forelse ($pekerjaanTerbaru as $order)
                        <div class="rounded-xl border border-slate-200 p-4 flex items-center justify-between gap-4 hover:border-[#6D28D9]/30 transition">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full border border-slate-300 bg-slate-50 text-slate-600">
                                        {{ $order->order_code }}
                                    </span>
                                    @if ($order->is_urgent)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">URGENT</span>
                                    @endif
                                    @if ($order->is_express)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">EXPRESS</span>
                                    @endif
                                </div>
                                <h3 class="font-semibold text-slate-900 mt-2 truncate">
                                    {{ $order->product?->name ?? 'Layanan' }}
                                </h3>
                                <p class="text-xs text-slate-500 mt-1.5">
                                    Biaya: <span class="font-semibold text-slate-800">{{ $rp($order->total_price) }}</span>
                                </p>
                            </div>
                            <a href="{{ route('designer.job-pool') }}"
                            class="shrink-0 text-xs font-semibold px-5 py-2 rounded-full bg-[#6D28D9] text-white hover:bg-[#5B21B6] transition">
                                Ambil
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 py-6 text-center">Belum ada pekerjaan baru di Job Pool.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-slate-900">Pendapatan Bulanan</h2>
                <p class="text-xs text-slate-500 mt-1 pb-4 border-b border-slate-100">
                    Tren 6 bulan terakhir (penghasilan bersih)
                </p>

                <div class="mt-4 relative h-40">
                    <canvas id="trenChart"></canvas>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-4 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#6D28D9]"></span>
                            Bulan Ini
                        </span>
                        <span class="font-semibold text-slate-900">{{ $rp($pendapatan['bulan_ini']) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            Bulan Lalu
                        </span>
                        <span class="font-semibold text-slate-900">{{ $rp($pendapatan['bulan_lalu']) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#D5FC55]"></span>
                            Target
                        </span>
                        <span class="font-semibold text-slate-900">{{ $rp($pendapatan['target']) }}</span>
                    </div>

                    <div class="pt-2">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="text-slate-600">Pencapaian Target</span>
                            <span class="font-semibold text-[#6D28D9]">{{ $persenBulanIni }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[#6D28D9] to-[#D5FC55] rounded-full transition-all duration-1000 ease-out"
                                style="width: 0%"
                                data-progress-target="{{ $persenBulanIni }}"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h2 class="text-lg font-semibold text-slate-900">Aktivitas Terbaru</h2>
                    <a href="{{ route('designer.notifikasi') }}" class="text-[11px] font-medium text-slate-500 hover:text-[#6D28D9]">Lihat Semua</a>
                </div>

                <ol class="relative ml-1 border-l border-slate-200 space-y-5">
                    @forelse ($notifikasi as $n)
                        <li class="pl-5 relative">
                            <span class="absolute -left-[4.5px] top-1.5 w-2 h-2 rounded-full bg-[#6D28D9]"></span>
                            <p class="text-sm font-semibold text-slate-800 leading-snug">{{ $n->judul }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $n->isi }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">{{ $n->waktu }}</p>
                        </li>
                    @empty
                        <li class="pl-5 text-sm text-slate-400">Belum ada aktivitas.</li>
                    @endforelse
                </ol>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const trenCtx = document.getElementById('trenChart');
        if (trenCtx && typeof Chart !== 'undefined') {
            const trenData = @json($trenBulanan);

            new Chart(trenCtx, {
                type: 'line',
                data: {
                    labels: trenData.map(d => d.bulan),
                    datasets: [{
                        label: 'Pendapatan',
                        data: trenData.map(d => d.total),
                        borderColor: '#6D28D9',
                        backgroundColor: 'rgba(109, 40, 217, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6D28D9',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e1b4b',
                            padding: 12,
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            callbacks: {
                                label: function (ctx) {
                                    return 'Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { size: 10 },
                                color: '#94a3b8',
                                callback: function (value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000) + 'jt';
                                    if (value >= 1000) return 'Rp ' + (value / 1000) + 'rb';
                                    return 'Rp ' + value;
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, color: '#94a3b8' }
                        }
                    },
                    animation: { duration: 1000, easing: 'easeOutQuart' }
                }
            });
        }

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && typeof Chart !== 'undefined') {
            const statusData = @json($statusBreakdown ?? []);

            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Dikerjakan', 'Menunggu Review', 'Revisi', 'Menunggu Keputusan'],
                    datasets: [{
                        data: [
                            statusData.in_progress || 0,
                            statusData.deliverable_sent || 0,
                            statusData.revision_needed || 0,
                            statusData.waiting_decision || 0,
                        ],
                        backgroundColor: ['#6D28D9', '#A78BFA', '#D5FC55', '#FBC003'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e1b4b',
                            padding: 10,
                            callbacks: {
                                label: function (ctx) {
                                    return ctx.label + ': ' + ctx.parsed;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 1200,
                        easing: 'easeOutQuart',
                    }
                }
            });
        }

        document.querySelectorAll('[data-animate-value]').forEach(el => {
            const end = parseFloat(el.dataset.animateValue) || 0;
            const format = el.dataset.format || 'int';
            const duration = 1500;
            const startTime = performance.now();

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = start + (end - start) * eased;

                if (format === 'currency') {
                    el.textContent = 'Rp ' + Math.round(current).toLocaleString('id-ID');
                } else if (format === 'rating') {
                    el.textContent = current.toFixed(1) + ' / 5.0';
                } else {
                    el.textContent = String(Math.round(current)).padStart(2, '0');
                }

                if (progress < 1) requestAnimationFrame(update);
            }

            const start = 0;
            requestAnimationFrame(update);
        });

        document.querySelectorAll('[data-progress-target]').forEach(el => {
            const target = el.dataset.progressTarget || 0;
            setTimeout(() => {
                el.style.width = target + '%';
            }, 200);
        });
    });
</script>
@endpush