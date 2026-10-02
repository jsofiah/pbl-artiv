@props(['variant' => 'light'])

@php
    $isDark = $variant === 'dark';
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex items-center gap-2 hover:opacity-80 transition">

        {{-- Avatar --}}
        <div class="w-9 h-9 rounded-full border-2 flex items-center justify-center font-semibold text-sm
                    {{ $isDark
                        ? 'bg-[#745BB8]/10 border-[#745BB8]/30 text-[#745BB8]'
                        : 'bg-[#D5FC55] border-white text-[#7C3AED]' }}">
            {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
        </div>

        {{-- Nama + role --}}
        <div class="hidden sm:block text-left">
            <p class="text-m font-medium leading-tight {{ $isDark ? 'text-gray-900' : 'text-white' }}">
                {{ auth()->user()->full_name ?? 'User' }}
            </p>
            <p class="text-sm capitalize leading-tight {{ $isDark ? 'text-[#745BB8]' : 'text-[#D5FC55]' }}">
                {{ auth()->user()->role ?? 'customer' }}
            </p>
        </div>

        {{-- Chevron --}}
        <x-heroicon-o-chevron-down class="w-4 h-4 {{ $isDark ? 'text-gray-400' : 'text-white' }}" />
    </button>

    {{-- Dropdown --}}
    <div x-show="open" @click.outside="open = false" x-transition
         class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-lg border border-gray-100 py-2 z-50">
        <a href="#" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Profil</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50">Keluar</button>
        </form>
    </div>
</div>