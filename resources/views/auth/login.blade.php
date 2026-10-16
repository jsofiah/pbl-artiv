<x-guest-layout>

    <div class="flex flex-col justify-center px-6 py-10 sm:px-12">
        <div class="mb-10 flex items-center gap-2">
            <img src="{{ asset('assets/logo.png') }}" alt="Artiv" class="h-10 w-auto object-contain object-left">
            <span class="text-3xl font-semibold text-[#7C3AED] lowercase tracking-tight">artiv</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900">Selamat datang kembali</h1>
        <p class="text-gray-500 mt-1 mb-8">Masuk untuk melanjutkan ke akunmu</p>

        @if (session('status'))
            <div class="mb-6 text-sm font-medium text-green-600">{{ session('status') }}</div>
        @endif

        <a
            href="{{ route('google.login') }}"
            class="flex items-center justify-center gap-3 w-full rounded-2xl border border-gray-200 bg-white hover:bg-gray-50 active:bg-gray-100 text-gray-700 font-medium py-3.5 text-sm transition"
        >
            <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Masuk dengan Google
        </a>

        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white px-3 text-gray-400 tracking-wider">atau</span>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-medium text-gray-500 mb-1.5">
                    Alamat Email
                </label>

                <div class="flex items-center gap-3 rounded-2xl border border-gray-200 px-4 py-3 focus-within:border-[#7C3AED] focus-within:ring-2 focus-within:ring-[#7C3AED]/15 transition">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nama@email.com"
                        class="w-full border-0 p-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 text-sm"
                    >
                </div>

                @error('email')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-gray-500 mb-1.5">
                    Kata Sandi
                </label>

                <div class="flex items-center gap-3 rounded-2xl border border-gray-200 px-4 py-3 focus-within:border-[#7C3AED] focus-within:ring-2 focus-within:ring-[#7C3AED]/15 transition">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full border-0 p-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 text-sm"
                    >

                    <button
                        type="button"
                        onclick="const i=document.getElementById('password'); i.type = i.type === 'password' ? 'text' : 'password';"
                        class="text-gray-400 hover:text-gray-600 shrink-0"
                        aria-label="Tampilkan atau sembunyikan kata sandi"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>

                @error('password')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input
                        type="checkbox"
                        name="remember"
                        class="rounded border-gray-300 text-[#7C3AED] focus:ring-[#7C3AED]/30"
                    >
                    Ingat saya
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-[#7C3AED] hover:underline">
                        Lupa kata sandi?
                    </a>
                @endif
            </div>

            <button
                type="submit"
                class="w-full mt-2 rounded-2xl bg-[#AE97DB] hover:bg-[#9E7ED3] text-white font-medium py-3.5 text-sm transition"
            >
                Masuk
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-6">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-[#7C3AED] font-medium hover:underline">
                Daftar di sini
            </a>
        </p>
    </div>

    <div class="hidden lg:block relative rounded-[24px] overflow-hidden bg-[#7C3AED] min-h-[600px]">
        <img src="{{ asset('assets/login-image.webp') }}" alt="Artiv" class="absolute inset-0 w-full h-full object-cover">
    </div>

</x-guest-layout>