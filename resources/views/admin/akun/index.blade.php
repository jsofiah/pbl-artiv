@extends('layouts.admin')

@section('title', 'Manajemen Akun')

@section('content')
@php
    $tabs = ['semua' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Dinonaktifkan'];
    $sorts = ['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'nama' => 'Nama A-Z'];
    $url = fn (array $extra = []) => route('admin.akun.' . $role) . '?' . http_build_query(array_filter(
        array_merge(['q' => $q, 'status' => $status, 'sort' => $sort], $extra), fn ($v) => $v !== '' && $v !== null));
@endphp

<div>
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Akun {{ $roleLabel }}</h1>
            <p class="text-slate-500 mt-1.5">Kelola data dan status akun {{ strtolower($roleLabel) }} yang terdaftar di Artiv.</p>
        </div>
        <button type="button" onclick="bukaFormAkun('create')" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-full bg-[#6D28D9] hover:bg-[#5B21B6] transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            Tambah {{ $roleLabel }}
        </button>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 mb-4 text-sm font-medium text-[#5B21B6] bg-[#6D28D9]/10 border border-[#6D28D9]/20 rounded-xl">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="px-4 py-3 mb-4 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-xl">{{ session('error') }}</div>
    @endif

    {{-- Search + tab + sort --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        {{-- Tabs --}}
        <div class="flex items-center gap-2 flex-wrap">
            @foreach ($tabs as $key => $label)
                <a href="{{ $url(['status' => $key, 'page' => null]) }}"
                   class="px-4 py-2 rounded-full text-sm font-semibold border transition
                          {{ $status === $key ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                    {{ $label }}
                    <span class="ml-1.5 inline-block rounded-full px-1.5 py-0.5 text-[11px] font-bold {{ $status === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $counts[$key] }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Search & Sort --}}
        <div class="flex items-center gap-2 flex-wrap ml-auto">
            <form method="GET" action="{{ route('admin.akun.' . $role) }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <div class="bg-white rounded-full shadow-sm border border-slate-200 p-1.5 flex items-center gap-2 w-[380px]">
                    <div class="pl-3 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama atau email..."
                           class="flex-1 bg-transparent border-none focus:outline-none text-slate-700 placeholder:text-slate-400 px-1 py-1.5 text-sm">
                    <button type="submit" class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-3 py-1.5 rounded-full transition text-sm whitespace-nowrap">
                        Cari
                    </button>
                </div>
            </form>

            {{-- Alpine Dropdown untuk Sort --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-4 pr-3 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[190px] justify-between">
                    <span>Urutkan: {{ $sorts[$sort] ?? 'Terbaru' }}</span>
                    <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 right-0" style="display: none;">
                    @foreach ($sorts as $value => $label)
                        <a href="{{ $url(['sort' => $value, 'page' => null]) }}" class="block w-full text-left px-5 py-2 text-sm transition {{ $sort === $value ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Grid kartu --}}
    @if ($akun->count())
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($akun as $u)
                @php
                    $aktif     = (bool) $u->is_active;
                    $avatarUrl = \App\Helpers\R2Helper::url($u->avatar_url);

                    $availability    = $u->designerStat?->availability_status ?? 'unavailable';
                    $availabilityUrl = $role === 'designer'
                        ? route('admin.akun.availability', $u)
                        : null;

                    $detail = [
                        'id'           => $u->id,
                        'nama'         => $u->full_name,
                        'email'        => $u->email,
                        'phone'        => $u->phone ?? '-',
                        'bergabung'    => optional($u->created_at)->translatedFormat('d F Y') ?? '-',
                        'login'        => optional($u->last_login_at ?? null)->diffForHumans() ?? '-',
                        'aktif'        => $aktif,
                        'role'         => $roleLabel,
                        'avatar'       => $avatarUrl,
                        'rating'       => $u->avg_rating ?? null,
                        'ulasan'       => $u->review_count ?? 0,
                        'selesai'      => $u->completed_order ?? 0,
                        'total'        => $u->total_order ?? 0,
                        'status_url'   => route('admin.akun.status', $u),
                        'availability' => $availability,
                    ];

                    $edit = [
                        'id'       => $u->id,
                        'nama'     => $u->full_name,
                        'username' => $u->username,
                        'email'    => $u->email,
                        'phone'    => $u->phone,
                        'aktif'    => $aktif,
                        'avatar'   => $avatarUrl,
                        'self'     => $u->id === auth()->id(),
                        'url'      => route('admin.akun.update', $u),
                    ];
                @endphp

                <div class="p-5 bg-white border border-slate-100 shadow-sm rounded-2xl {{ $aktif ? '' : 'opacity-70' }}">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-sm font-semibold rounded-full {{ $aktif ? 'bg-[#6D28D9]/10 text-[#5B21B6]' : 'bg-slate-100 text-slate-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $aktif ? 'bg-[#6D28D9]' : 'bg-slate-400' }}"></span>
                        {{ $aktif ? 'Aktif' : 'Dinonaktifkan' }}
                    </span>

                    <div class="flex flex-col items-center mt-3 text-center">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="" class="object-cover w-16 h-16 rounded-full border-2 border-[#6D28D9]/20">
                        @else
                            <div class="flex items-center justify-center w-16 h-16 text-lg font-bold rounded-full bg-[#6D28D9]/10 text-[#6D28D9]">
                                {{ strtoupper(mb_substr($u->full_name, 0, 1)) }}
                            </div>
                        @endif
                        <p class="mt-3 font-semibold text-slate-900">{{ $u->full_name }}</p>
                        <p class="text-sm text-slate-500">{{ $u->email }}</p>
                    </div>

                    @if ($role !== 'admin')
                        <div class="grid grid-cols-2 py-3 my-4 text-center border-y border-slate-100">
                            <div>
                                <p class="text-sm tracking-wide uppercase text-slate-400">Total Order</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $u->total_order ?? 0 }}</p>
                            </div>
                            <div class="border-l border-slate-100">
                                @if ($role === 'designer')
                                    <p class="text-sm tracking-wide uppercase text-slate-400">Rating</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $u->avg_rating ?? 0 }} / 5.0</p>
                                @else
                                    <p class="text-sm tracking-wide uppercase text-slate-400">Status</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $aktif ? 'Aktif' : 'Nonaktif' }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="my-4 border-t border-slate-100"></div>
                    @endif

                    {{-- Dropdown Status Availability --}}
                    @if ($role === 'designer')
                        @php
                            $availConfig = [
                                'available'   => ['label' => 'Available',   'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                                'unavailable' => ['label' => 'Unavailable', 'bg' => 'bg-slate-100',  'text' => 'text-slate-600',   'border' => 'border-slate-200',   'dot' => 'bg-slate-400'],
                                'suspended'   => ['label' => 'Suspended',   'bg' => 'bg-rose-50',    'text' => 'text-rose-700',    'border' => 'border-rose-200',    'dot' => 'bg-rose-500'],
                            ];
                            $cfg = $availConfig[$availability] ?? $availConfig['unavailable'];
                        @endphp

                        <div x-data="{ open: false }" class="relative mb-3">
                            <button type="button" @click="open = !open"
                                    class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-1.5 text-sm font-semibold border rounded-full transition {{ $cfg['bg'] }} {{ $cfg['text'] }} {{ $cfg['border'] }} hover:shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full {{ $cfg['dot'] }}"></span>
                                <span>Status: {{ $cfg['label'] }}</span>
                                <svg class="w-3.5 h-3.5 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-cloak
                                 class="absolute left-0 z-30 w-full py-1 mt-1 bg-white border shadow-lg border-slate-200 rounded-xl"
                                 style="display: none;">
                                @foreach (['available', 'unavailable', 'suspended'] as $opt)
                                    @php $o = $availConfig[$opt]; @endphp
                                    <form method="POST" action="{{ $availabilityUrl }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="availability_status" value="{{ $opt }}">
                                        <button type="submit"
                                                class="flex items-center w-full gap-2 px-3 py-2 text-sm text-left transition
                                                       {{ $availability === $opt ? 'bg-[#6D28D9]/10 text-[#5B21B6] font-semibold' : 'text-slate-600 hover:bg-[#6D28D9]/10 hover:text-[#5B21B6]' }}">
                                            <span class="w-2 h-2 rounded-full {{ $o['dot'] }}"></span>
                                            {{ $o['label'] }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button type="button" data-detail="{{ json_encode($detail) }}" onclick="bukaDetailAkun(this)"
                            class="w-full py-2.5 text-sm font-semibold text-white rounded-full bg-[#6D28D9] hover:bg-[#5B21B6] transition shadow-sm">
                        Lihat Selengkapnya &rsaquo;
                    </button>

                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <button type="button"
                                data-edit-id="{{ $u->id }}"
                                data-edit="{{ json_encode($edit) }}"
                                onclick="bukaFormAkun('edit', this)"
                                class="w-full py-2 text-sm font-medium border rounded-full border-slate-200 text-slate-600 hover:bg-slate-50 transition">Edit</button>
                        <button type="button"
                                data-nama="{{ $u->full_name }}"
                                data-url="{{ route('admin.akun.destroy', $u) }}"
                                onclick="bukaHapusAkun(this)"
                                class="w-full py-2 text-sm font-medium border rounded-full border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600 transition">Hapus</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 pt-5 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
            <p class="text-sm text-slate-500">
                Menampilkan {{ $akun->firstItem() }} - {{ $akun->lastItem() }} dari {{ $akun->total() }} {{ $roleLabel }}
            </p>

            @if ($akun->hasPages())
                <div class="flex items-center gap-2">
                    <a href="{{ $akun->previousPageUrl() ?? '#' }}"
                        class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $akun->onFirstPage() ? 'text-slate-300 pointer-events-none' : 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' }} transition">
                        &lt; Sebelumnya
                    </a>
                    @for ($i = 1; $i <= $akun->lastPage(); $i++)
                        <a href="{{ $akun->url($i) }}"
                            class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-semibold transition
                                    {{ $i === $akun->currentPage()
                                        ? 'bg-[#6D28D9] text-white shadow-sm'
                                        : 'border border-slate-200 bg-white text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                            {{ $i }}
                        </a>
                    @endfor
                    <a href="{{ $akun->nextPageUrl() ?? '#' }}"
                        class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $akun->hasMorePages() ? 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' : 'text-slate-300 pointer-events-none' }} transition">
                        Selanjutnya &gt;
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="py-16 text-center bg-white border border-dashed border-slate-300 rounded-2xl">
            <p class="font-semibold text-slate-700">Tidak ada akun ditemukan</p>
            <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter.</p>
        </div>
    @endif
</div>

<div id="modalDetailAkun" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50" onclick="if(event.target===this) tutupDetailAkun()">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto p-7 bg-white shadow-2xl rounded-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Detail Akun</h2>
            <button type="button" onclick="tutupDetailAkun()" class="flex items-center justify-center w-9 h-9 rounded-full bg-[#6D28D9]/10 text-[#6D28D9] hover:bg-[#6D28D9]/20">&times;</button>
        </div>

        <div class="flex items-center gap-4 p-4 mt-5 rounded-2xl bg-[#6D28D9]/5">
            <div id="dAvatar" class="flex items-center justify-center w-16 h-16 overflow-hidden text-xl font-bold rounded-full bg-[#6D28D9]/15 text-[#6D28D9]"></div>
            <div>
                <p id="dNama" class="text-lg font-bold text-slate-900"></p>
                <div class="flex flex-wrap gap-1.5 mt-1.5 text-sm font-semibold">
                    <span id="dRole" class="px-2.5 py-1 text-white uppercase rounded-full bg-[#6D28D9]"></span>
                    <span id="dStatus" class="px-2.5 py-1 rounded-full"></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-3">
            @foreach ([['dEmail','Email'],['dPhone','Nomor Telepon'],['dBergabung','Tanggal Bergabung'],['dLogin','Login Terakhir']] as [$id,$label])
                <div class="p-3.5 rounded-xl bg-[#6D28D9]/5">
                    <p class="text-sm font-semibold tracking-wide uppercase text-slate-500">{{ $label }}</p>
                    <p id="{{ $id }}" class="mt-1 text-sm font-medium break-words text-slate-800"></p>
                </div>
            @endforeach
        </div>

        <div id="dDesigner" class="hidden p-4 mt-3 rounded-2xl bg-[#6D28D9]/5">
            <p class="mb-3 font-semibold text-slate-800">Informasi Spesifik Designer</p>
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-white rounded-xl">
                    <p class="text-sm font-semibold text-slate-500">Rating Rata-rata</p>
                    <p class="mt-1 text-sm"><b id="dRating"></b> <span id="dUlasan" class="text-sm text-slate-500"></span></p>
                </div>
                <div class="p-3 bg-white rounded-xl">
                    <p class="text-sm font-semibold text-slate-500">Penyelesaian Order</p>
                    <p id="dSelesai" class="mt-1 text-sm font-semibold"></p>
                </div>
                <div class="p-3 bg-white rounded-xl col-span-2">
                    <p class="text-sm font-semibold text-slate-500">Status Availability</p>
                    <p id="dAvailability" class="mt-1 text-sm font-semibold"></p>
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-6">
            <form id="dToggleForm" method="POST">
                @csrf @method('PATCH')
                <button id="dToggleBtn" class="px-5 py-2.5 text-sm font-semibold text-slate-900 rounded-full bg-[#D5FC55] hover:bg-[#C5EC45]"></button>
            </form>
        </div>
    </div>
</div>

<div id="modalFormAkun" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto p-7 bg-white shadow-2xl rounded-3xl">
        <div class="flex items-center justify-between">
            <h2 id="fJudul" class="text-2xl font-bold text-slate-900"></h2>
            <button type="button" onclick="tutupFormAkun()" class="flex items-center justify-center w-9 h-9 rounded-full bg-[#6D28D9]/10 text-[#6D28D9] hover:bg-[#6D28D9]/20">&times;</button>
        </div>

        <form id="fForm" method="POST" action="" enctype="multipart/form-data" class="mt-5 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="fMethod" value="PUT" disabled>
            <input type="hidden" name="_mode" id="fMode">
            <input type="hidden" name="_user" id="fUser">
            <input type="hidden" name="hapus_foto" id="fHapusFoto" value="0">

            {{-- Foto profil --}}
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-[#6D28D9]/5">
                <div id="fPreview" class="flex items-center justify-center w-20 h-20 overflow-hidden text-2xl font-bold rounded-full shrink-0 bg-[#6D28D9]/15 text-[#6D28D9]"></div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800">Foto Profil</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <label for="f_avatar" class="px-3.5 py-1.5 text-sm font-semibold text-white rounded-full cursor-pointer bg-[#6D28D9] hover:bg-[#5B21B6]">Pilih Foto</label>
                        <button type="button" id="fHapusBtn" onclick="hapusFotoForm()"
                                class="hidden px-3.5 py-1.5 text-sm font-semibold rounded-full text-red-600 bg-red-50 hover:bg-red-100">Hapus Foto</button>
                    </div>
                    <input id="f_avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="pilihFotoForm(this)">
                    <p class="mt-1.5 text-sm text-slate-500">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    <p id="fFotoErr" class="mt-1 text-sm text-red-600">{{ $errors->first('avatar') }}</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="f_full_name" class="block mb-1.5 text-sm font-semibold text-slate-700">Nama Lengkap</label>
                    <input id="f_full_name" name="full_name" type="text" required placeholder="Masukkan nama lengkap"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="f_username" class="block mb-1.5 text-sm font-semibold text-slate-700">Username</label>
                    <input id="f_username" name="username" type="text" required placeholder="contoh: budi_santoso"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="f_phone" class="block mb-1.5 text-sm font-semibold text-slate-700">Nomor Telepon</label>
                    <input id="f_phone" name="phone" type="text" placeholder="08xxxxxxxxxx"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="f_email" class="block mb-1.5 text-sm font-semibold text-slate-700">Email</label>
                    <input id="f_email" name="email" type="email" required placeholder="nama@artiv.com"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="f_password" id="fPassLabel" class="block mb-1.5 text-sm font-semibold text-slate-700">Password</label>
                    <input id="f_password" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="f_password_confirmation" class="block mb-1.5 text-sm font-semibold text-slate-700">Konfirmasi Password</label>
                    <input id="f_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Ulangi password"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                </div>
            </div>

            <label id="fAktifWrap" class="flex items-center gap-3 p-4 cursor-pointer rounded-xl bg-[#6D28D9]/5">
                <input type="hidden" name="is_active" value="0">
                <input id="f_is_active" type="checkbox" name="is_active" value="1" class="w-4 h-4 accent-[#6D28D9]">
                <span>
                    <span class="block text-sm font-semibold text-slate-800">Akun aktif</span>
                    <span class="block text-sm text-slate-500">Matikan untuk menonaktifkan akun ini.</span>
                </span>
            </label>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" onclick="tutupFormAkun()" class="px-5 py-2.5 text-sm font-semibold rounded-full text-[#6D28D9] bg-[#6D28D9]/10 hover:bg-[#6D28D9]/20">Batal</button>
                <button type="submit" id="fSubmit" class="px-5 py-2.5 text-sm font-semibold text-white rounded-full bg-[#6D28D9] hover:bg-[#5B21B6]"></button>
            </div>
        </form>
    </div>
</div>

<div id="modalHapusAkun" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50" onclick="if(event.target===this) tutupHapusAkun()">
    <div class="w-full max-w-sm p-6 text-center bg-white shadow-2xl rounded-3xl">
        <div class="flex items-center justify-center w-14 h-14 mx-auto text-2xl rounded-full bg-red-50 text-red-500">!</div>
        <h2 class="mt-4 text-lg font-bold text-slate-900">Yakin mau hapus akun?</h2>
        <p class="mt-2 text-sm text-slate-500">
            Akun <b id="hNama" class="text-slate-800"></b> akan dihapus dan tidak bisa dikembalikan.
        </p>

        <form id="hForm" method="POST" class="flex items-center justify-center gap-3 mt-6">
            @csrf
            @method('DELETE')
            <button type="button" onclick="tutupHapusAkun()" class="px-5 py-2.5 text-sm font-semibold rounded-full text-[#6D28D9] bg-[#6D28D9]/10 hover:bg-[#6D28D9]/20">Batal</button>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white rounded-full bg-red-600 hover:bg-red-700">Ya, Hapus</button>
        </form>
    </div>
</div>

<script>
    const modalAkun = document.getElementById('modalDetailAkun');
    const $ = (id) => document.getElementById(id);

    function bukaDetailAkun(btn) {
        const d = JSON.parse(btn.dataset.detail);

        $('dNama').textContent = d.nama;
        $('dRole').textContent = d.role;
        $('dEmail').textContent = d.email;
        $('dPhone').textContent = d.phone;
        $('dBergabung').textContent = d.bergabung;
        $('dLogin').textContent = d.login;

        setAvatar($('dAvatar'), d.avatar, d.nama);

        const st = $('dStatus');
        st.textContent = d.aktif ? 'Status: Aktif' : 'Status: Dinonaktifkan';
        st.className = 'px-2.5 py-1 rounded-full ' + (d.aktif ? 'bg-white text-[#5B21B6]' : 'bg-slate-200 text-slate-500');

        const isDesigner = d.role === 'Designer';
        $('dDesigner').classList.toggle('hidden', !isDesigner);
        if (isDesigner) {
            $('dRating').textContent = '★ ' + (d.rating ?? '0');
            $('dUlasan').textContent = `/ 5.0 (${d.ulasan} Ulasan)`;
            $('dSelesai').textContent = `${d.selesai} dari ${d.total} order`;

            const availLabel = {
                'available': 'Available',
                'unavailable': 'Unavailable',
                'suspended': 'Suspended',
            }[d.availability] ?? d.availability;
            $('dAvailability').textContent = availLabel;
        }

        $('dToggleForm').action = d.status_url;
        $('dToggleBtn').textContent = d.aktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun';

        modalAkun.classList.remove('hidden');
        modalAkun.classList.add('flex');
    }

    function tutupDetailAkun() {
        modalAkun.classList.add('hidden');
        modalAkun.classList.remove('flex');
    }

    // Tampilkan foto (kalau ada) atau huruf inisial
    function setAvatar(box, url, nama) {
        box.replaceChildren();
        if (url) {
            const img = document.createElement('img');
            img.src = url;
            img.alt = '';
            img.className = 'object-cover w-full h-full';
            box.appendChild(img);
        } else {
            box.textContent = (nama || '?').charAt(0).toUpperCase();
        }
    }

    // ===== Popup Tambah / Edit =====
    const modalForm = document.getElementById('modalFormAkun');
    const urlStore  = @json(route('admin.akun.store', $role));
    const judulRole = @json($roleLabel);

    function bukaFormAkun(mode, btn = null, old = null) {
        const edit = mode === 'edit';
        const d = btn ? JSON.parse(btn.dataset.edit) : {};
        const v = old || {
            full_name: d.nama, username: d.username, email: d.email,
            phone: d.phone, aktif: edit ? d.aktif : true,
        };

        $('fForm').reset();
        $('fForm').action = edit ? d.url : urlStore;
        $('fMethod').disabled = !edit;
        $('fMode').value = mode;
        $('fUser').value = d.id || '';
        $('fHapusFoto').value = '0';
        $('fFotoErr').textContent = '';

        $('fJudul').textContent = (edit ? 'Edit Akun ' : 'Tambah Akun ') + judulRole;
        $('fSubmit').textContent = edit ? 'Simpan Perubahan' : 'Simpan Akun';
        $('fPassLabel').textContent = edit ? 'Password Baru (opsional)' : 'Password';
        $('f_password').required = !edit;

        $('f_full_name').value = v.full_name ?? '';
        $('f_username').value  = v.username ?? '';
        $('f_email').value     = v.email ?? '';
        $('f_phone').value     = v.phone ?? '';
        $('f_is_active').checked = !!v.aktif;
        $('fAktifWrap').classList.toggle('hidden', !!d.self);

        setPreview(edit ? d.avatar : null);

        modalForm.classList.remove('hidden');
        modalForm.classList.add('flex');
    }

    function tutupFormAkun() {
        modalForm.classList.add('hidden');
        modalForm.classList.remove('flex');
    }

    function setPreview(url) {
        setAvatar($('fPreview'), url, $('f_full_name').value);
        $('fHapusBtn').classList.toggle('hidden', !url);
    }

    function pilihFotoForm(input) {
        const file = input.files[0];
        $('fFotoErr').textContent = '';
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            $('fFotoErr').textContent = 'Ukuran foto maksimal 2 MB.';
            input.value = '';
            return;
        }
        $('fHapusFoto').value = '0';
        setPreview(URL.createObjectURL(file));
    }

    function hapusFotoForm() {
        $('f_avatar').value = '';
        $('fHapusFoto').value = '1';
        setPreview(null);
    }

    @if (old('_mode'))
        // Validasi gagal: buka lagi popup dengan isian sebelumnya
        document.addEventListener('DOMContentLoaded', () => {
            const old = {
                full_name: @json(old('full_name')),
                username:  @json(old('username')),
                email:     @json(old('email')),
                phone:     @json(old('phone')),
                aktif:     @json(old('is_active') === '1'),
            };
            if (@json(old('_mode')) === 'edit') {
                const btn = document.querySelector('[data-edit-id="' + @json(old('_user')) + '"]');
                if (btn) { bukaFormAkun('edit', btn, old); return; }
            }
            bukaFormAkun('create', null, old);
        });
    @endif

    const modalHapus = document.getElementById('modalHapusAkun');

    function bukaHapusAkun(btn) {
        $('hNama').textContent = btn.dataset.nama;
        $('hForm').action = btn.dataset.url;
        modalHapus.classList.remove('hidden');
        modalHapus.classList.add('flex');
    }

    function tutupHapusAkun() {
        modalHapus.classList.add('hidden');
        modalHapus.classList.remove('flex');
    }

    // Tutup semua popup dengan tombol Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            tutupDetailAkun();
            tutupFormAkun();
            tutupHapusAkun();
        }
    });
</script>

@endsection