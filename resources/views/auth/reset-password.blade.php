<x-guest-layout>
    <div class="flex flex-col justify-center px-6 py-10 sm:px-12 lg:col-span-2 max-w-md mx-auto w-full">

        <div class="mb-8 flex items-center gap-2">
            <img
                src="{{ asset('assets/logo.png') }}"
                alt="Artiv"
                class="h-10 w-auto object-contain object-left"
            >
            <span class="text-3xl font-semibold text-[#7C3AED] lowercase tracking-tight">artiv</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900">
            Atur ulang kata sandi
        </h1>

        <p class="text-gray-500 mt-1 mb-8 text-sm">
            Buat kata sandi baru untuk akunmu.
        </p>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="block text-xs font-medium text-gray-500 mb-1.5">
                    Alamat Email
                </label>

                <div class="flex items-center gap-3 rounded-2xl border border-gray-200 px-4 py-3 focus-within:border-[#7C3AED] focus-within:ring-2 focus-within:ring-[#7C3AED]/15 transition">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $request->email) }}"
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
                    Kata Sandi Baru
                </label>

                <div class="flex items-center gap-3 rounded-2xl border border-gray-200 px-4 py-3 focus-within:border-[#7C3AED] focus-within:ring-2 focus-within:ring-[#7C3AED]/15 transition">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
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

            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-gray-500 mb-1.5">
                    Konfirmasi Kata Sandi
                </label>

                <div class="flex items-center gap-3 rounded-2xl border border-gray-200 px-4 py-3 focus-within:border-[#7C3AED] focus-within:ring-2 focus-within:ring-[#7C3AED]/15 transition">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full border-0 p-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 text-sm"
                    >

                    <button
                        type="button"
                        onclick="const i=document.getElementById('password_confirmation'); i.type = i.type === 'password' ? 'text' : 'password';"
                        class="text-gray-400 hover:text-gray-600 shrink-0"
                        aria-label="Tampilkan atau sembunyikan kata sandi"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>

                @error('password_confirmation')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full rounded-2xl bg-[#AE97DB] hover:bg-[#9E7ED3] text-white font-medium py-3.5 text-sm transition"
                >
                    Atur Ulang Kata Sandi
                </button>
            </div>

        </form>

    </div>

</x-guest-layout>