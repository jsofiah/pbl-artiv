<nav x-data="{ open: false }" class="bg-[#745BB8] shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <div class="flex items-center gap-2">
                <img src="{{ asset('assets/logo-putih.png') }}" alt="Artiv" class="h-8 w-auto">
                <span class="text-xl font-semibold text-white lowercase">artiv</span>
            </div>

            <div class="hidden lg:flex items-center gap-2 bg-[#FFFFFF]/15 border-2 border-[#FFFFFF]/20 rounded-full px-3 py-1">
                <a href="{{ route('customer.beranda') }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition
                            {{ request()->routeIs('customer.beranda')
                                ? 'bg-[#D5FC55] text-neutral-900 font-semibold'
                                : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    Beranda
                </a>
                @php
                    $isKatalogActive = request()->routeIs('customer.katalog')
                                    || request()->routeIs('customer.katalog.*')
                                    || request()->routeIs('customer.pemesanan.*');
                @endphp

                <a href="{{ route('customer.katalog') }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition
                            {{ $isKatalogActive
                                ? 'bg-[#D5FC55] text-neutral-900 font-semibold'
                                : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    Katalog
                </a>
                <a href="{{ route('customer.pesanan') }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition
                    {{ request()->routeIs('customer.pesanan*')
                    ? 'bg-[#D5FC55] text-neutral-900 font-semibold'
                    : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    Pesanan Saya
                </a>
                <a href="{{ route('customer.galeri') }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition
                            {{ request()->routeIs('customer.galeri')
                                ? 'bg-[#D5FC55] text-neutral-900 font-semibold'
                                : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    Galeri
                </a>
        </div>

            <div class="flex items-center gap-4">
                @auth
                   @include('partials.notification-dropdown')
                    <x-shared.user-menu />
                @else
                    <a href="{{ route('login') }}"
                    class="px-5 py-2 rounded-full bg-[#D5FC55] text-neutral-900 text-sm font-semibold hover:bg-[#c5ec45] transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                    class="px-5 py-2 rounded-full border-2 border-white/30 text-white text-sm font-medium hover:bg-white/10 transition">
                        Daftar
                    </a>
                @endauth

                <div class="-me-2 flex items-center lg:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-[#D5FC55] focus:outline-none transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden bg-[#745BB8]">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('customer.beranda') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('customer.beranda') ? 'border-[#D5FC55] text-[#D5FC55] bg-[#FFFFFF]/10' : 'border-transparent text-white hover:text-[#D5FC55] hover:bg-[#FFFFFF]/5' }}">
                Beranda
            </a>
            <a href="{{ route('customer.katalog') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ $isKatalogActive ? 'border-[#D5FC55] text-[#D5FC55] bg-[#FFFFFF]/10' : 'border-transparent text-white hover:text-[#D5FC55] hover:bg-[#FFFFFF]/5' }}">
                Katalog
            </a>
            <a href="{{ route('customer.pesanan') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('customer.pesanan*') ? 'border-[#D5FC55] text-[#D5FC55] bg-[#FFFFFF]/10' : 'border-transparent text-white hover:text-[#D5FC55] hover:bg-[#FFFFFF]/5' }}">
                Pesanan Saya
            </a>
            <a href="{{ route('customer.galeri') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('customer.galeri') ? 'border-[#D5FC55] text-[#D5FC55] bg-[#FFFFFF]/10' : 'border-transparent text-white hover:text-[#D5FC55] hover:bg-[#FFFFFF]/5' }}">
                Galeri
            </a>
        </div>
        
        @guest
            <div class="pt-4 pb-3 border-t border-white/20 px-4 space-y-2">
                <a href="{{ route('login') }}" class="block w-full text-center py-2 rounded-full bg-[#D5FC55] text-neutral-900 text-sm font-semibold hover:bg-[#c5ec45] transition">Masuk</a>
                <a href="{{ route('register') }}" class="block w-full text-center py-2 rounded-full border-2 border-white/30 text-white text-sm font-medium hover:bg-white/10 transition">Daftar</a>
            </div>
        @endguest
    </div>
</nav>
</nav>