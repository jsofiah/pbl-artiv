@props(['variant' => 'light'])

@php
    $isDark = $variant === 'dark';
    $user = auth()->user();
    $avatarUrl = $user->avatar_url ? \App\Helpers\R2Helper::url($user->avatar_url) : null;
@endphp

<div x-data="{ open: false }" class="relative">
    {{-- Tombol Trigger --}}
    <button type="button" @click="open = !open" class="flex items-center gap-2 hover:opacity-80 transition focus:outline-none">

        {{-- Avatar --}}
        <div class="w-9 h-9 rounded-full border-2 overflow-hidden flex items-center justify-center font-semibold text-sm shrink-0
                    {{ $isDark
                        ? 'bg-[#745BB8]/10 border-[#745BB8]/30 text-[#745BB8]'
                        : 'bg-[#D5FC55] border-white text-[#7C3AED]' }}">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $user->full_name }}" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr($user->full_name ?? 'U', 0, 1)) }}
            @endif
        </div>

        {{-- Nama + role --}}
        <div class="hidden sm:block text-left">
            <p class="text-sm font-medium leading-tight {{ $isDark ? 'text-gray-900' : 'text-white' }}">
                {{ $user->full_name ?? 'User' }}
            </p>
            <p class="text-xs capitalize leading-tight {{ $isDark ? 'text-[#745BB8]' : 'text-[#D5FC55]' }}">
                {{ $user->role ?? 'customer' }}
            </p>
        </div>

        <svg class="w-4 h-4 transition-transform duration-200 {{ $isDark ? 'text-gray-400' : 'text-white' }}"
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown Menu --}}
    <div x-show="open" 
         @click.outside="open = false"
         class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-2xl shadow-lg py-2 z-50"
         style="display: none;">
        
        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9] transition">
            Profil
        </a>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition">
                Keluar
            </button>
        </form>
    </div>
</div>