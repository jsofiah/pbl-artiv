@props(['variant' => 'light'])

@php
    $isDark = $variant === 'dark';
@endphp

<a href="#"
   class="relative p-2 rounded-full border-2 transition
          {{ $isDark
                ? 'bg-[#745BB8]/10 border-[#745BB8]/20 text-[#745BB8] hover:text-[#5F52A8]'
                : 'bg-[#FFFFFF]/15 border-[#FFFFFF]/20 text-white hover:text-[#D5FC55]' }}">

    <x-heroicon-o-bell class="w-5 h-5" />

    {{-- Badge notifikasi — aktifkan kalau tabel notifications sudah siap --}}
    {{--
    @if (auth()->user()->unreadNotifications->count() > 0)
        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
    @endif
    --}}
</a>