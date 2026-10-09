@php
    $user = auth()->user();
    
    $layout = 'layouts.customer';
    if ($user->role === 'admin') {
        $layout = 'layouts.admin';
    } elseif ($user->role === 'designer') {
        $layout = 'layouts.designer';
    }
@endphp

@extends($layout)

@section('title', 'Profil Saya')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-8">

        <!-- Header / Breadcrumb -->
        <div class="mb-2">
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900">
                Profil Saya
            </h1>
            <p class="text-sm text-neutral-600 mt-1">
                Kelola informasi identitas pribadi, keamanan akun, dan pantau ringkasan aktivitas transaksi Anda.
            </p>
        </div>

        <!-- Card 1: Informasi Pribadi & Kontak -->
        <div class="bg-white rounded-3xl shadow-sm border border-neutral-200/80 p-6 sm:p-8">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-neutral-100">
                <div>
                    <h2 class="text-lg font-bold text-neutral-900">Informasi Pribadi & Kontak</h2>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold 
                    {{ $user->isAdmin() ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                    {{ $user->isDesigner() ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                    {{ $user->isCustomer() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full 
                        {{ $user->isAdmin() ? 'bg-blue-500' : '' }}
                        {{ $user->isDesigner() ? 'bg-purple-500' : '' }}
                        {{ $user->isCustomer() ? 'bg-emerald-500' : '' }}"></span>
                    {{ ucfirst($user->role) }} Terverifikasi
                </span>
            </div>

            <!-- Avatar & Upload Section -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 mb-8 bg-neutral-50/60 border border-neutral-100 rounded-2xl p-5">
                <div class="relative shrink-0">
                    @if($user->avatar_url)
                        <img src="{{ \App\Helpers\R2Helper::url($user->avatar_url) }}" alt="{{ $user->full_name }}" class="w-20 h-20 rounded-full object-cover shadow-sm">
                    @else
                        <div class="w-20 h-20 rounded-full bg-[#745BB8] text-white flex items-center justify-center font-bold text-2xl shadow-sm">
                            {{ strtoupper(substr($user->full_name, 0, 2)) }}
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></span>
                </div>

                <div class="space-y-2 flex-1">
                    <!-- Form Upload Foto Terpisah -->
                    <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                        @csrf
                        <label class="cursor-pointer font-semibold text-sm text-[#745BB8] hover:underline">
                            <span>Ubah Foto</span>
                            <input type="file" name="avatar" accept="image/png, image/jpeg, image/jpg, image/gif" class="hidden" onchange="this.form.submit()">
                        </label>

                        @if($user->avatar_url)
                            <span class="text-neutral-300">|</span>
                        @endif
                    </form>

                    <!-- Form Hapus Foto (jika ada) -->
                    @if($user->avatar_url)
                        <form action="{{ route('profile.avatar.destroy') }}" method="POST">
                            @csrf
                            @method('delete')
                            <button type="submit" class="text-xs text-neutral-500 hover:text-red-600 transition">Hapus Foto</button>
                        </form>
                    @endif

                    @error('avatar')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Update Profile -->
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('patch')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" required
                            class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-2xl text-sm text-neutral-900 focus:outline-none focus:border-[#745BB8] focus:bg-white transition">
                        @error('full_name')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-2">Username</label>
                        <div class="relative flex items-center">
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                                class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-2xl text-sm text-neutral-900 focus:outline-none focus:border-[#745BB8] focus:bg-white transition">
                            <span class="absolute right-4 text-neutral-400 text-sm">@</span>
                        </div>
                        @error('username')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-2">Alamat Email</label>
                        <div class="relative flex items-center">
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-2xl text-sm text-neutral-900 focus:outline-none focus:border-[#745BB8] focus:bg-white transition">
                            <span class="absolute right-4 text-neutral-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </span>
                        </div>
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-2">Nomor Telepon / WhatsApp</label>
                        <div class="relative flex items-center">
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+62 88888888888"
                                class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-2xl text-sm text-neutral-900 focus:outline-none focus:border-[#745BB8] focus:bg-white transition">
                            <span class="absolute right-4 text-neutral-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </span>
                        </div>
                        @error('phone')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="px-6 py-3 bg-[#745BB8] hover:bg-[#6349a7] text-white font-bold text-sm rounded-2xl shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Keamanan Akun & Kata Sandi -->
        <div class="bg-white rounded-3xl shadow-sm border border-neutral-200/80 p-6 sm:p-8">
            <div class="mb-6 pb-4 border-b border-neutral-100">
                <h2 class="text-lg font-bold text-neutral-900">Keamanan Akun & Kata Sandi</h2>
                <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">Pastikan kata sandi Anda menggunakan kombinasi karakter unik untuk menjaga keamanan akun Anda.</p>
            </div>

            <!-- Form Update Password -->
            <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('put')

                <!-- Password Lama -->
                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-2">Password Lama</label>
                    <div class="flex items-center gap-3 rounded-2xl border border-gray-200 px-4 py-3 focus-within:border-[#745BB8] focus-within:ring-2 focus-within:ring-[#745BB8]/15 transition bg-neutral-50">
                        <input
                            id="current_password"
                            type="password"
                            name="current_password"
                            required
                            placeholder="••••••••••••"
                            class="w-full border-0 p-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 text-sm bg-transparent"
                        >
                        <button
                            type="button"
                            onclick="const i=document.getElementById('current_password'); i.type = i.type === 'password' ? 'text' : 'password';"
                            class="text-gray-400 hover:text-gray-600 shrink-0 focus:outline-none"
                            aria-label="Tampilkan atau sembunyikan kata sandi"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Password Baru -->
                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-2">Password Baru</label>
                        <div class="flex items-center gap-3 rounded-2xl border border-gray-200 px-4 py-3 focus-within:border-[#745BB8] focus-within:ring-2 focus-within:ring-[#745BB8]/15 transition bg-neutral-50">
                            <input
                                id="new_password"
                                type="password"
                                name="password"
                                required
                                placeholder="Minimal 8 karakter"
                                class="w-full border-0 p-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 text-sm bg-transparent"
                            >
                            <button
                                type="button"
                                onclick="const i=document.getElementById('new_password'); i.type = i.type === 'password' ? 'text' : 'password';"
                                class="text-gray-400 hover:text-gray-600 shrink-0 focus:outline-none"
                                aria-label="Tampilkan atau sembunyikan kata sandi"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-2">Konfirmasi Password Baru</label>
                        <div class="flex items-center gap-3 rounded-2xl border border-gray-200 px-4 py-3 focus-within:border-[#745BB8] focus-within:ring-2 focus-within:ring-[#745BB8]/15 transition bg-neutral-50">
                            <input
                                id="new_password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                placeholder="Ulangi password baru"
                                class="w-full border-0 p-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 text-sm bg-transparent"
                            >
                            <button
                                type="button"
                                onclick="const i=document.getElementById('new_password_confirmation'); i.type = i.type === 'password' ? 'text' : 'password';"
                                class="text-gray-400 hover:text-gray-600 shrink-0 focus:outline-none"
                                aria-label="Tampilkan atau sembunyikan kata sandi"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="px-6 py-3 bg-[#745BB8] hover:bg-[#6349a7] text-white font-bold text-sm rounded-2xl shadow-sm transition">
                        Perbarui Password
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection