<nav class="bg-[#745BB8] shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <div class="flex items-center gap-2">
                <img src="{{ asset('assets/logo-putih.png') }}" alt="Artiv" class="h-8 w-auto">
                <span class="text-xl font-semibold text-white lowercase">artiv</span>
            </div>

            <!-- Menu Tengah -->
            <div class="hidden md:flex items-center gap-2 bg-[#FFFFFF]/15 border-2 border-[#FFFFFF]/20 rounded-full px-3 py-1">
                <a href="{{ route('customer.beranda') }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition
                          {{ request()->routeIs('customer.beranda')
                                ? 'bg-[#D5FC55] text-neutral-900 font-semibold'
                                : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    Beranda
                </a>
                <a href="{{ route('customer.katalog') }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition
                          {{ request()->routeIs('customer.katalog')
                                ? 'bg-[#D5FC55] text-neutral-900 font-semibold'
                                : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    Katalog
                </a>
                <a href="{{ route('customer.pesanan') }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition
                          {{ request()->routeIs('customer.pesanan')
                                ? 'bg-[#D5FC55] text-neutral-900 font-semibold'
                                : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    Pesanan Saya
                </a>
            </div>

            <!-- Kanan: Notif + Akun -->
            <div class="flex items-center gap-4">
                @auth
                    {{-- Sudah login --}}
                    <x-shared.notification-icon />
                    <x-shared.user-menu />
                @else
                    {{-- Belum login --}}
                    <a href="{{ route('login') }}"
                    class="px-5 py-2 rounded-full bg-[#D5FC55] text-neutral-900 text-sm font-semibold hover:bg-[#c5ec45] transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                    class="px-5 py-2 rounded-full border-2 border-white/30 text-white text-sm font-medium hover:bg-white/10 transition">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>