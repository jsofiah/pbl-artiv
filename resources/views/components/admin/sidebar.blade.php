@php
    $menus = [
        ['type' => 'link',     'label' => 'Dashboard',                'route' => 'admin.dashboard',      'icon' => 'home'],
        ['type' => 'dropdown', 'label' => 'Manajemen Akun',           'icon' => 'users', 'children' => [
            ['route' => 'admin.akun.designer', 'label' => 'Akun Designer'],
            ['route' => 'admin.akun.admin',    'label' => 'Akun Admin'],
            ['route' => 'admin.akun.customer', 'label' => 'Akun Customer'],
        ]],
        ['type' => 'link',     'label' => 'Manajemen Katalog',        'route' => 'admin.katalog',        'icon' => 'photo'],
        ['type' => 'link',     'label' => 'Manajemen Harga Express',  'route' => 'admin.harga-express',  'icon' => 'currency-dollar'],
        ['type' => 'link',     'label' => 'Manajemen File',           'route' => 'admin.file',           'icon' => 'folder'],
        ['type' => 'link',     'label' => 'Monitoring Pekerjaan',     'route' => 'admin.monitoring',     'icon' => 'chart-bar'],
        ['type' => 'link',     'label' => 'Laporan & Statistik',      'route' => 'admin.laporan',        'icon' => 'document-chart-bar'],
        ['type' => 'link',     'label' => 'Pengaturan',               'route' => 'admin.pengaturan',     'icon' => 'cog-6-tooth'],
    ];
@endphp

<aside class="flex h-screen w-64 flex-col justify-between bg-[#745BB8]">

    <div class="overflow-y-auto">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            <img src="{{ asset('assets/logo-putih.png') }}" alt="Artiv" class="h-8 w-auto">
            <div class="leading-tight">
                <p class="text-m font-semibold text-white lowercase">artiv</p>
                <p class="text-sm text-white/60">Admin Panel</p>
            </div>
        </div>

        {{-- Menu --}}
        <nav class="px-4 pt-6 pb-6">
            <p class="mb-2 px-2 text-xs font-medium tracking-wide text-white/50 uppercase">Menu</p>

            <ul class="space-y-0.5">
                @foreach ($menus as $menu)

                    {{-- ================= LINK BIASA ================= --}}
                    @if ($menu['type'] === 'link')
                        @php $active = request()->routeIs($menu['route']); @endphp
                        <li>
                            <a href="{{ route($menu['route']) }}"
                               class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                                      {{ $active
                                            ? 'bg-white/20 text-white font-semibold'
                                            : 'text-white/70 hover:bg-white/10 hover:text-white' }}">

                                @if ($active)
                                    <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-[#D5FC55]"></span>
                                @endif

                                <span class="flex h-5 w-5 items-center justify-center {{ $active ? 'text-[#D5FC55]' : 'text-white/60 group-hover:text-white' }}">
                                    <x-dynamic-component :component="'heroicon-o-' . $menu['icon']" class="w-5 h-5" />
                                </span>

                                <span class="flex-1">{{ $menu['label'] }}</span>
                            </a>
                        </li>

                    {{-- ================= DROPDOWN ================= --}}
                    @elseif ($menu['type'] === 'dropdown')
                        @php $parentActive = request()->routeIs('admin.akun.*'); @endphp
                        <li x-data="{ open: {{ $parentActive ? 'true' : 'false' }} }">

                            <button @click="open = !open"
                                class="group w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                                       {{ $parentActive
                                            ? 'bg-white/20 text-white font-semibold'
                                            : 'text-white/70 hover:bg-white/10 hover:text-white' }}">

                                <span class="flex h-5 w-5 items-center justify-center {{ $parentActive ? 'text-[#D5FC55]' : 'text-white/60 group-hover:text-white' }}">
                                    <x-dynamic-component :component="'heroicon-o-' . $menu['icon']" class="w-5 h-5" />
                                </span>

                                <span class="flex-1 text-left">{{ $menu['label'] }}</span>

                                <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform"
                                                            x-bind:class="open && 'rotate-180'" />
                            </button>

                            {{-- Children --}}
                            <ul x-show="open" x-collapse class="mt-1 ml-4 pl-3 border-l border-white/20 space-y-1">
                                @foreach ($menu['children'] as $child)
                                    @php $childActive = request()->routeIs($child['route']); @endphp
                                    <li>
                                        <a href="{{ route($child['route']) }}"
                                           class="block px-3 py-2 text-sm rounded-lg transition-colors
                                                  {{ $childActive
                                                        ? 'bg-white/20 text-[#D5FC55] font-medium'
                                                        : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                                            {{ $child['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif

                @endforeach
            </ul>
        </nav>

    </div>

    {{-- User & Logout --}}
    <div class="border-t border-white/10 p-4">

        <div class="mb-3 flex items-center gap-3 px-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/20 text-sm font-semibold text-white">
                {{ strtoupper(substr(auth()->user()->full_name ?? 'AD', 0, 1)) }}
            </div>
            <div class="min-w-0 leading-tight">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->full_name ?? 'Admin' }}</p>
                <p class="text-xs text-white/60 capitalize">{{ auth()->user()->role ?? 'admin' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#D5FC55] py-2.5 text-sm font-semibold text-[#4A3A7A] transition hover:bg-[#C0E83F]">
                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                Keluar
            </button>
        </form>

    </div>
</aside>