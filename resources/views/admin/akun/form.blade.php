@extends('layouts.admin')

@section('title', 'Manajemen Akun')

@section('content')
@php
    $isEdit = isset($user);
    $isSelf = $isEdit && $user->id === auth()->id();
    $action = $isEdit ? route('admin.akun.update', $user) : route('admin.akun.store', $role);
    $back   = route('admin.akun.' . $role);
    $input  = 'w-full px-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9] ';

    $fields = [
        ['full_name', 'Nama Lengkap', 'text',  'Masukkan nama lengkap', $user->full_name ?? ''],
        ['username',  'Username',     'text',  'contoh: budi_santoso', $user->username ?? ''],
        ['email',     'Email',        'email', 'nama@artiv.com',       $user->email ?? ''],
        ['phone',     'Nomor Telepon','text',  '08xxxxxxxxxx',         $user->phone ?? ''],
    ];
@endphp

<div class="p-6 lg:p-8 max-w-3xl">

    <h1 class="text-3xl font-bold text-slate-900">{{ $isEdit ? 'Edit' : 'Tambah' }} Akun {{ $roleLabel }}</h1>
    <p class="mt-1 text-sm text-slate-500">
        {{ $isEdit ? 'Ubah data akun ' . strtolower($roleLabel) . '. Kosongkan password jika tidak ingin menggantinya.' : 'Isi data berikut untuk membuat akun ' . strtolower($roleLabel) . ' baru.' }}
    </p>

    <form method="POST" action="{{ $action }}" class="p-6 mt-6 space-y-5 bg-white border border-slate-200 rounded-2xl">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($fields as [$name, $label, $type, $placeholder, $value])
                <div class="{{ $name === 'full_name' || $name === 'email' ? 'sm:col-span-2' : '' }}">
                    <label for="{{ $name }}" class="block mb-1.5 text-sm font-semibold text-slate-700">{{ $label }}</label>
                    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
                           placeholder="{{ $placeholder }}"
                           class="{{ $input }} {{ $errors->has($name) ? 'border-red-400' : 'border-slate-200' }}">
                    @error($name) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach

            <div>
                <label for="password" class="block mb-1.5 text-sm font-semibold text-slate-700">
                    {{ $isEdit ? 'Password Baru' : 'Password' }}
                </label>
                <input id="password" name="password" type="password" autocomplete="new-password"
                       placeholder="Minimal 8 karakter"
                       class="{{ $input }} {{ $errors->has('password') ? 'border-red-400' : 'border-slate-200' }}">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block mb-1.5 text-sm font-semibold text-slate-700">Konfirmasi Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                       placeholder="Ulangi password"
                       class="{{ $input }} border-slate-200">
            </div>
        </div>

        @unless ($isSelf)
            <label class="flex items-center gap-3 p-4 rounded-xl bg-[#6D28D9]/5 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="w-4 h-4 accent-[#6D28D9]"
                       @checked(old('is_active', $isEdit ? $user->is_active : true))>
                <span>
                    <span class="block text-sm font-semibold text-slate-800">Akun aktif</span>
                    <span class="block text-xs text-slate-500">Matikan untuk menonaktifkan akun ini.</span>
                </span>
            </label>
        @endunless

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ $back }}" class="px-5 py-2.5 text-sm font-semibold rounded-lg text-[#6D28D9] bg-[#6D28D9]/10 hover:bg-[#6D28D9]/20">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white rounded-lg bg-[#6D28D9] hover:bg-[#5B21B6]">
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Akun' }}
            </button>
        </div>
    </form>

    <a href="{{ $back }}" class="inline-flex items-center gap-1 mt-5 text-sm font-medium text-[#6D28D9] hover:underline">
        &lsaquo; Kembali ke Akun {{ $roleLabel }}
    </a>
</div>
@endsection
