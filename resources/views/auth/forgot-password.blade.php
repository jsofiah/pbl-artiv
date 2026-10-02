<x-guest-layout>

    <!-- Form (full-width, center) -->
    <div class="flex flex-col justify-center px-6 py-10 sm:px-12 lg:col-span-2 max-w-md mx-auto w-full">

        <!-- Brand -->
        <div class="mb-8 flex items-center gap-2">
            <img
                src="{{ asset('assets/logo.png') }}"
                alt="Artiv"
                class="h-10 w-auto object-contain object-left"
            >
            <span class="text-3xl font-semibold text-[#7C3AED] lowercase tracking-tight">artiv</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900">
            Lupa kata sandi?
        </h1>

        <p class="text-gray-500 mt-1 mb-8 text-sm">
            Masukkan email kamu, dan kami akan mengirimkan link untuk mengatur ulang kata sandi.
        </p>

        @if (session('status'))
            <div class="mb-6 text-sm font-medium text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email -->
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
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="w-full border-0 p-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 text-sm"
                    >
                </div>

                @error('email')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full rounded-2xl bg-[#AE97DB] hover:bg-[#9E7ED3] text-white font-medium py-3.5 text-sm transition"
                >
                    Kirim Link Reset
                </button>

                <p class="text-center text-sm text-gray-600 mt-4">
                    Ingat kata sandimu?
                    <a href="{{ route('login') }}" class="text-[#7C3AED] font-medium hover:underline">
                        Kembali ke login
                    </a>
                </p>
            </div>

        </form>

    </div>

</x-guest-layout>