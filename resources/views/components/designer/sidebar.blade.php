@php
    $menus = [
        ['label' => 'Dashboard',          'route' => 'designer.dashboard',        'icon' => 'grid'],
        ['label' => 'Job Pool',           'route' => 'designer.job-pool',         'icon' => 'briefcase'],
        ['label' => 'Pekerjaan Saya',     'route' => 'designer.pekerjaan-saya',   'icon' => 'check'],
        ['label' => 'Riwayat Pekerjaan',  'route' => 'designer.riwayat',          'icon' => 'history'],
    ];
@endphp

<aside class="flex h-screen w-64 flex-col justify-between bg-[#745BB8]">

    <div>

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            <img src="{{ asset('assets/logo-putih.png') }}" alt="Artiv" class="h-8 w-auto">
            <div class="leading-tight">
                <p class="text-m font-semibold text-white lowercase">artiv</p>
                <p class="text-sm text-white/60">Designer Panel</p>
            </div>
        </div>

        {{-- Menu --}}
        <nav class="px-4 pt-6">
            <p class="mb-2 px-2 text-xs font-medium tracking-wide text-white/50 uppercase">Menu</p>

            <ul class="space-y-1">
                @foreach ($menus as $menu)
                    @php $active = request()->routeIs($menu['route']); @endphp
                    <li>
                        <a href="{{ route($menu['route']) }}"
                           class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                                  {{ $active
                                        ? 'bg-white/20 text-white font-semibold'
                                        : 'text-white/70 hover:bg-white/10 hover:text-white' }}">

                            {{-- Indikator aktif (strip kuning) --}}
                            @if ($active)
                                <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-[#D5FC55]"></span>
                            @endif

                            {{-- Ikon --}}
                            <span class="flex h-5 w-5 items-center justify-center {{ $active ? 'text-[#D5FC55]' : 'text-white/60 group-hover:text-white' }}">
                                @switch($menu['icon'])
                                    @case('grid')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                            <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                                            <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                                            <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                                            <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                                        </svg>
                                        @break
                                    @case('briefcase')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                            <rect x="3" y="7" width="18" height="13" rx="2"/>
                                            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        @break
                                    @case('check')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                            <rect x="3" y="4" width="18" height="17" rx="2"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8 12 3 3 5-6"/>
                                        </svg>
                                        @break
                                    @case('history')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                            <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                            <path stroke-linecap="round" d="M3 4v5h5"/>
                                            <path stroke-linecap="round" d="M12 7v5l3.5 2"/>
                                        </svg>
                                        @break
                                @endswitch
                            </span>

                            <span class="flex-1">{{ $menu['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

    </div>

    {{-- User & Logout --}}
    <div class="border-t border-white/10 p-4">

        <div class="mb-3 flex items-center gap-3 px-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/20 text-sm font-semibold text-white">
                {{ strtoupper(substr(auth()->user()->full_name ?? 'DS', 0, 1)) }}
            </div>
            <div class="min-w-0 leading-tight">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->full_name ?? 'Designer' }}</p>
                <p class="text-xs text-white/60 capitalize">{{ auth()->user()->role ?? 'designer' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#D5FC55] py-2.5 text-sm font-semibold text-[#4A3A7A] transition hover:bg-[#C0E83F]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5-5-5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12H9" />
                </svg>
                Keluar
            </button>
        </form>

    </div>
</aside>