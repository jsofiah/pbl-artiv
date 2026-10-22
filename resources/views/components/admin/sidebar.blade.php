@php
    $menuGroups = [
        [
            'title' => 'MENU',
            'items' => [
                ['type' => 'link', 'label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home'],
            ]
        ],
        [
            'title' => 'MANAJEMEN',
            'items' => [
                [
                    'type' => 'dropdown', 
                    'label' => 'Manajemen Akun', 
                    'icon' => 'users', 
                    'prefix' => 'admin.akun.*',
                    'children' => [
                        ['route' => 'admin.akun.designer', 'label' => 'Akun Designer'],
                        ['route' => 'admin.akun.admin',    'label' => 'Akun Admin'],
                        ['route' => 'admin.akun.customer', 'label' => 'Akun Customer'],
                    ]
                ],
                ['type' => 'link', 'label' => 'Manajemen Katalog',       'route' => 'admin.katalog',       'icon' => 'photo'],
                ['type' => 'link', 'label' => 'Manajemen Harga Express', 'route' => 'admin.harga-express', 'icon' => 'currency-dollar'],
                ['type' => 'link', 'label' => 'Manajemen File',          'route' => 'admin.file',          'icon' => 'folder'],
            ]
        ],
        [
            'title' => 'OPERASIONAL',
            'items' => [
                ['type' => 'link', 'label' => 'Monitoring Pekerjaan',    'route' => 'admin.monitoring',    'icon' => 'chart-bar'],
                ['type' => 'link', 'label' => 'Laporan & Statistik',     'route' => 'admin.laporan',       'icon' => 'document-chart-bar'],
            ]
        ],
        [
            'title' => 'SISTEM',
            'items' => [
                ['type' => 'link', 'label' => 'Pengaturan',              'route' => 'admin.pengaturan',    'icon' => 'cog-6-tooth'],
            ]
        ],
    ];
@endphp

<aside class="sticky top-0 flex h-screen w-64 shrink-0 flex-col justify-between bg-[#745BB8] select-none">

    <div class="overflow-y-auto no-scrollbar">

        {{-- Logo & Brand --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            <img src="{{ asset('assets/logo-putih.png') }}" alt="Artiv" class="h-8 w-auto">
            <div class="leading-tight">
                <p class="text-base font-bold text-white lowercase tracking-wide">artiv</p>
                <p class="text-xs text-white/60 font-medium">Admin Panel</p>
            </div>
        </div>

        {{-- Navigasi Menu Berkelompok --}}
        <nav class="px-4 py-4 space-y-6">
            @foreach ($menuGroups as $group)
                <div>
                    {{-- Header Kategori --}}
                    <p class="mb-2 px-3 text-[10px] font-bold tracking-wider text-white/40 uppercase">
                        {{ $group['title'] }}
                    </p>

                    <ul class="space-y-1">
                        @foreach ($group['items'] as $item)

                            {{-- ================= LINK BIASA ================= --}}
                            @if ($item['type'] === 'link')
                                @php 
                                    $active = request()->routeIs($item['route']); 
                                    $targetUrl = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                                @endphp
                                <li>
                                    <a href="{{ $targetUrl }}"
                                       class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs transition-all duration-150
                                              {{ $active
                                                    ? 'bg-white/20 text-white font-bold shadow-sm'
                                                    : 'text-white/70 hover:bg-white/10 hover:text-white font-medium' }}">

                                        @if ($active)
                                            <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-[#D5FC55]"></span>
                                        @endif

                                        <span class="flex h-5 w-5 items-center justify-center shrink-0 {{ $active ? 'text-[#D5FC55]' : 'text-white/60 group-hover:text-white' }}">
                                            <x-dynamic-component :component="'heroicon-o-' . $item['icon']" class="w-4 h-4" />
                                        </span>

                                        <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                    </a>
                                </li>

                            {{-- ================= DROPDOWN ================= --}}
                            @elseif ($item['type'] === 'dropdown')
                                @php 
                                    $parentActive = request()->routeIs($item['prefix'] ?? 'admin.akun.*'); 
                                @endphp
                                <li x-data="{ open: {{ $parentActive ? 'true' : 'false' }} }">

                                    <button type="button" @click="open = !open"
                                        class="group w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs transition-all duration-150
                                               {{ $parentActive
                                                    ? 'bg-white/20 text-white font-bold'
                                                    : 'text-white/70 hover:bg-white/10 hover:text-white font-medium' }}">

                                        <span class="flex h-5 w-5 items-center justify-center shrink-0 {{ $parentActive ? 'text-[#D5FC55]' : 'text-white/60 group-hover:text-white' }}">
                                            <x-dynamic-component :component="'heroicon-o-' . $item['icon']" class="w-4 h-4" />
                                        </span>

                                        <span class="flex-1 text-left truncate">{{ $item['label'] }}</span>

                                        <x-heroicon-o-chevron-down class="w-3.5 h-3.5 transition-transform duration-200"
                                                                    x-bind:class="open && 'rotate-180'" />
                                    </button>

                                    {{-- Submenu Children --}}
                                    <ul x-show="open" x-collapse class="mt-1 ml-4 pl-3 border-l border-white/20 space-y-1">
                                        @foreach ($item['children'] as $child)
                                            @php 
                                                $childActive = request()->routeIs($child['route']); 
                                                $childUrl = \Illuminate\Support\Facades\Route::has($child['route']) ? route($child['route']) : '#';
                                            @endphp
                                            <li>
                                                <a href="{{ $childUrl }}"
                                                   class="block px-3 py-2 text-xs rounded-lg transition-colors
                                                          {{ $childActive
                                                                ? 'bg-white/20 text-[#D5FC55] font-bold'
                                                                : 'text-white/70 hover:bg-white/10 hover:text-white font-medium' }}">
                                                    {{ $child['label'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

    </div>

    {{-- User Profile & Logout Bottom Bar --}}
    <div class="border-t border-white/10 p-4">

        <div class="mb-3 flex items-center gap-3 px-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/20 text-xs font-bold text-white shrink-0">
                {{ strtoupper(substr(auth()->user()->full_name ?? 'AD', 0, 1)) }}
            </div>
            <div class="min-w-0 leading-tight">
                <p class="text-xs font-bold text-white truncate">{{ auth()->user()->full_name ?? 'Admin' }}</p>
                <p class="text-[11px] text-white/60 capitalize">{{ auth()->user()->role ?? 'admin' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#D5FC55] py-2.5 text-xs font-bold text-[#4A3A7A] shadow-sm transition hover:bg-[#C0E83F]">
                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                Keluar
            </button>
        </form>

    </div>
</aside>