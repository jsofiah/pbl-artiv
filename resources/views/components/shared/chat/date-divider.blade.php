@props(['date'])

@php
    use Carbon\Carbon;

    $carbon    = $date instanceof Carbon ? $date : Carbon::parse($date);
    $today     = now()->startOfDay();
    $yesterday = now()->subDay()->startOfDay();
    $msgDay    = $carbon->copy()->startOfDay();

    if ($msgDay->equalTo($today)) {
        $label = 'Hari Ini';
    } elseif ($msgDay->equalTo($yesterday)) {
        $label = 'Kemarin';
    } else {
        $label = $carbon->translatedFormat('d F Y');
    }
@endphp

<div class="flex items-center gap-3 py-2">
    <div class="flex-1 h-px bg-slate-200"></div>
    <span class="px-3 py-1 rounded-full bg-slate-100 text-xs font-medium text-slate-500">
        {{ $label }}
    </span>
    <div class="flex-1 h-px bg-slate-200"></div>
</div>