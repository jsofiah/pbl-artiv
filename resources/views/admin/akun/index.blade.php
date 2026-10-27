@extends('layouts.admin')

@section('title', 'Manajemen Akun')

@section('content')
@php
    $tabs = ['semua' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Dinonaktifkan'];
    $sorts = ['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'nama' => 'Nama A-Z'];
    $url = fn (array $extra = []) => route('admin.akun.' . $role) . '?' . http_build_query(array_filter(
        array_merge(['q' => $q, 'status' => $status, 'sort' => $sort], $extra), fn ($v) => $v !== '' && $v !== null));
@endphp

<div class="p-6 lg:p-8">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Akun {{ $roleLabel }}</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola data dan status akun {{ strtolower($roleLabel) }} yang terdaftar di Artiv.</p>
        </div>
        <button type="button" onclick="bukaFormAkun('create')"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white rounded-lg bg-[#6D28D9] hover:bg-[#5B21B6]">
            + Tambah {{ $roleLabel }}
        </button>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 mb-4 text-sm text-[#5B21B6] bg-[#6D28D9]/10 border border-[#6D28D9]/20 rounded-lg">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="px-4 py-3 mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Search + tab + sort --}}
    <form method="GET" action="{{ route('admin.akun.' . $role) }}"
          class="flex flex-wrap items-center justify-between gap-3 p-4 mb-6 bg-white border border-slate-200 rounded-xl">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ $url(['status' => $key, 'page' => null]) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-md border
                          {{ $status === $key ? 'bg-[#6D28D9] text-white border-[#6D28D9]' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100' }}">
                    {{ $label }} ({{ $counts[$key] }})
                </a>
            @endforeach
        </div>
        <div class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama atau email..."
                   class="w-56 px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
            <select name="sort" onchange="this.form.submit()"
                    class="py-2 pl-3 pr-9 text-sm border rounded-lg border-slate-200 text-slate-600 focus:outline-none focus:border-[#6D28D9]">
                @foreach ($sorts as $key => $label)
                    <option value="{{ $key }}" @selected($sort === $key)>Urutkan: {{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Grid kartu --}}
    @if ($akun->count())
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($akun as $u)
                @php
                    $aktif = (bool) $u->is_active;
                    $avatarUrl = \App\Helpers\R2Helper::url($u->avatar_url);
                    $detail = [
                        'id'        => $u->id,
                        'nama'      => $u->full_name,
                        'email'     => $u->email,
                        'phone'     => $u->phone ?? '-',
                        'bergabung' => optional($u->created_at)->translatedFormat('d F Y') ?? '-',
                        'login'     => optional($u->last_login_at ?? null)->diffForHumans() ?? '-',
                        'aktif'     => $aktif,
                        'role'      => $roleLabel,
                        'avatar'    => $avatarUrl,
                        'rating'    => $u->avg_rating ?? null,
                        'ulasan'    => $u->review_count ?? 0,
                        'selesai'   => $u->completed_order ?? 0,
                        'total'     => $u->total_order ?? 0,
                        'status_url'=> route('admin.akun.status', $u),
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
                <div class="p-5 bg-white border border-slate-200 rounded-2xl {{ $aktif ? '' : 'opacity-70' }}">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-full
                                 {{ $aktif ? 'bg-[#6D28D9]/10 text-[#5B21B6]' : 'bg-slate-100 text-slate-500' }}">
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
                        <p class="text-xs text-slate-500">{{ $u->email }}</p>
                    </div>

                    @if ($role !== 'admin')
                        <div class="grid grid-cols-2 py-3 my-4 text-center border-y border-slate-100">
                            <div>
                                <p class="text-[10px] tracking-wide uppercase text-slate-400">Total Order</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $u->total_order ?? 0 }}</p>
                            </div>
                            <div class="border-l border-slate-100">
                                @if ($role === 'designer')
                                    <p class="text-[10px] tracking-wide uppercase text-slate-400">Rating</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $u->avg_rating ?? 0 }} / 5.0</p>
                                @else
                                    <p class="text-[10px] tracking-wide uppercase text-slate-400">Status</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $aktif ? 'Aktif' : 'Nonaktif' }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="my-4 border-t border-slate-100"></div>
                    @endif

                    <button type="button" data-detail="{{ json_encode($detail) }}" onclick="bukaDetailAkun(this)"
                            class="w-full py-2.5 text-sm font-semibold text-white rounded-lg bg-[#6D28D9] hover:bg-[#5B21B6]">
                        Lihat Selengkapnya &rsaquo;
                    </button>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <button type="button"
                                data-edit-id="{{ $u->id }}"
                                data-edit="{{ json_encode($edit) }}"
                                onclick="bukaFormAkun('edit', this)"
                                class="w-full py-2 text-xs font-medium border rounded-lg border-slate-200 text-slate-600 hover:bg-slate-50">Edit</button>
                        <button type="button"
                                data-nama="{{ $u->full_name }}"
                                data-url="{{ route('admin.akun.destroy', $u) }}"
                                onclick="bukaHapusAkun(this)"
                                class="w-full py-2 text-xs font-medium border rounded-lg border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600">Hapus</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-5 mt-6 border-t border-slate-200">
            <p class="text-xs text-slate-500">
                Menampilkan <b>{{ $akun->firstItem() }} - {{ $akun->lastItem() }}</b> dari <b>{{ $akun->total() }}</b> {{ $roleLabel }}
            </p>
            {{ $akun->links('vendor.pagination.artiv') }}
        </div>
    @else
        <div class="py-16 text-center bg-white border border-dashed border-slate-300 rounded-2xl">
            <p class="font-semibold text-slate-700">Tidak ada akun ditemukan</p>
            <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter.</p>
        </div>
    @endif
</div>

{{-- Popup Detail Akun --}}
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
                <div class="flex flex-wrap gap-1.5 mt-1.5 text-[11px] font-semibold">
                    <span id="dRole" class="px-2.5 py-1 text-white uppercase rounded-full bg-[#6D28D9]"></span>
                    <span id="dStatus" class="px-2.5 py-1 rounded-full"></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-3">
            @foreach ([['dEmail','Email'],['dPhone','Nomor Telepon'],['dBergabung','Tanggal Bergabung'],['dLogin','Login Terakhir']] as [$id,$label])
                <div class="p-3.5 rounded-xl bg-[#6D28D9]/5">
                    <p class="text-[10px] font-semibold tracking-wide uppercase text-slate-500">{{ $label }}</p>
                    <p id="{{ $id }}" class="mt-1 text-sm font-medium break-words text-slate-800"></p>
                </div>
            @endforeach
        </div>

        <div id="dDesigner" class="hidden p-4 mt-3 rounded-2xl bg-[#6D28D9]/5">
            <p class="mb-3 font-semibold text-slate-800">Informasi Spesifik Designer</p>
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-white rounded-xl">
                    <p class="text-xs font-semibold text-slate-500">Rating Rata-rata</p>
                    <p class="mt-1 text-sm"><b id="dRating"></b> <span id="dUlasan" class="text-xs text-slate-500"></span></p>
                </div>
                <div class="p-3 bg-white rounded-xl">
                    <p class="text-xs font-semibold text-slate-500">Penyelesaian Order</p>
                    <p id="dSelesai" class="mt-1 text-sm font-semibold"></p>
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

{{-- Popup Tambah / Edit Akun --}}
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
                        <label for="f_avatar" class="px-3.5 py-1.5 text-xs font-semibold text-white rounded-full cursor-pointer bg-[#6D28D9] hover:bg-[#5B21B6]">Pilih Foto</label>
                        <button type="button" id="fHapusBtn" onclick="hapusFotoForm()"
                                class="hidden px-3.5 py-1.5 text-xs font-semibold rounded-full text-red-600 bg-red-50 hover:bg-red-100">Hapus Foto</button>
                    </div>
                    <input id="f_avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="pilihFotoForm(this)">
                    <p class="mt-1.5 text-[11px] text-slate-500">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    <p id="fFotoErr" class="mt-1 text-xs text-red-600">{{ $errors->first('avatar') }}</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="f_full_name" class="block mb-1.5 text-sm font-semibold text-slate-700">Nama Lengkap</label>
                    <input id="f_full_name" name="full_name" type="text" required placeholder="Masukkan nama lengkap"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('full_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="f_username" class="block mb-1.5 text-sm font-semibold text-slate-700">Username</label>
                    <input id="f_username" name="username" type="text" required placeholder="contoh: budi_santoso"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="f_phone" class="block mb-1.5 text-sm font-semibold text-slate-700">Nomor Telepon</label>
                    <input id="f_phone" name="phone" type="text" placeholder="08xxxxxxxxxx"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="f_email" class="block mb-1.5 text-sm font-semibold text-slate-700">Email</label>
                    <input id="f_email" name="email" type="email" required placeholder="nama@artiv.com"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="f_password" id="fPassLabel" class="block mb-1.5 text-sm font-semibold text-slate-700">Password</label>
                    <input id="f_password" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter"
                           class="w-full px-3.5 py-2.5 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30 focus:border-[#6D28D9]">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                    <span class="block text-xs text-slate-500">Matikan untuk menonaktifkan akun ini.</span>
                </span>
            </label>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" onclick="tutupFormAkun()" class="px-5 py-2.5 text-sm font-semibold rounded-full text-[#6D28D9] bg-[#6D28D9]/10 hover:bg-[#6D28D9]/20">Batal</button>
                <button type="submit" id="fSubmit" class="px-5 py-2.5 text-sm font-semibold text-white rounded-full bg-[#6D28D9] hover:bg-[#5B21B6]"></button>
            </div>
        </form>
    </div>
</div>

{{-- Popup Konfirmasi Hapus --}}
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

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { tutupDetailAkun(); tutupHapusAkun(); tutupFormAkun(); }
    });
</script>
@endsection