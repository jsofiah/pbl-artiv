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

        <!-- Icon email verification -->
        <div class="mb-6 w-14 h-14 rounded-2xl bg-[#7C3AED]/10 flex items-center justify-center">
            <svg class="w-7 h-7 text-[#7C3AED]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
        </div>

        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900">
            Verifikasi email kamu
        </h1>

        <p class="text-gray-500 mt-2 mb-8 text-sm leading-relaxed">
            Terima kasih sudah mendaftar! Sebelum memulai, silakan verifikasi alamat email kamu dengan mengklik link yang baru saja kami kirimkan. Kalau tidak menerima emailnya, kami akan dengan senang hati mengirim ulang.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 px-4 py-3 text-sm font-medium text-green-700">
                Link verifikasi baru telah dikirim ke alamat email yang kamu daftarkan.
            </div>
        @endif

        <div class="space-y-3">

            <!-- Resend Verification Email -->
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full rounded-2xl bg-[#AE97DB] hover:bg-[#9E7ED3] text-white font-medium py-3.5 text-sm transition"
                >
                    Kirim Ulang Email Verifikasi
                </button>
            </form>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full rounded-2xl border border-gray-200 hover:border-gray-300 text-gray-600 hover:text-gray-900 font-medium py-3.5 text-sm transition"
                >
                    Keluar
                </button>
            </form>

        </div>

    </div>

</x-guest-layout>