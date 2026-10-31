<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Models\DesignerStat;
use App\Services\R2StorageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AkunController extends Controller
{
    private const ROLES = [
        'designer' => 'Designer',
        'admin'    => 'Admin',
        'customer' => 'Customer',
    ];

    // ==================== Daftar ====================

    public function index(Request $request, string $role = 'designer')
    {
        abort_unless(isset(self::ROLES[$role]), 404);

        $status = $request->query('status', 'semua');   // semua | aktif | nonaktif
        $sort   = $request->query('sort', 'terbaru');   // terbaru | terlama | nama
        $q      = trim((string) $request->query('q', ''));

        // Hitungan untuk tab status (hanya berdasarkan role)
        $base   = User::where('role', $role);
        $counts = [
            'semua'    => (clone $base)->count(),
            'aktif'    => (clone $base)->where('is_active', true)->count(),
            'nonaktif' => (clone $base)->where('is_active', false)->count(),
        ];

        $query = User::where('role', $role);

        // Statistik per akun (subquery, tanpa N+1)
        if ($role === 'designer') {
            $query->with('designerStat')          // ← eager load status availability
                  ->addSelect('users.*')->addSelect([
                      'total_order'     => Order::selectRaw('count(*)')->whereColumn('designer_id', 'users.id'),
                      'completed_order' => Order::selectRaw('count(*)')->whereColumn('designer_id', 'users.id')->where('status', 'completed'),
                      'avg_rating'      => Review::selectRaw('round(avg(rating), 1)')->whereColumn('designer_id', 'users.id'),
                      'review_count'    => Review::selectRaw('count(*)')->whereColumn('designer_id', 'users.id'),
                  ]);
        } elseif ($role === 'customer') {
            $query->addSelect('users.*')->addSelect([
                'total_order' => Order::selectRaw('count(*)')->whereColumn('customer_id', 'users.id'),
            ]);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('full_name', 'ilike', "%{$q}%")
                  ->orWhere('email', 'ilike', "%{$q}%");
            });
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        match ($sort) {
            'terlama' => $query->orderBy('created_at'),
            'nama'    => $query->orderBy('full_name'),
            default   => $query->orderByDesc('created_at'),
        };

        $akun = $query->paginate(15)->withQueryString();

        return view('admin.akun.index', [
            'role'      => $role,
            'roleLabel' => self::ROLES[$role],
            'akun'      => $akun,
            'counts'    => $counts,
            'status'    => $status,
            'sort'      => $sort,
            'q'         => $q,
        ]);
    }

    // ==================== Tambah (popup di halaman daftar) ====================

    public function store(Request $request, string $role, R2StorageService $r2)
    {
        abort_unless(isset(self::ROLES[$role]), 404);

        $data = $this->validated($request);
        unset($data['avatar']);

        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            try {
                $avatarUrl = $r2->upload($request->file('avatar'), 'avatars', R2StorageService::DISK_PUBLIC);
            } catch (\Throwable $e) {
                Log::error('Gagal upload avatar (tambah akun): ' . $e->getMessage());
                return back()->withInput()->with('error', 'Foto gagal diunggah. Coba lagi beberapa saat.');
            }
        }

        User::create($data + [
            'role'       => $role,
            'is_active'  => $request->boolean('is_active', true),
            'avatar_url' => $avatarUrl,
        ]);

        return redirect()
            ->route('admin.akun.' . $role)
            ->with('success', 'Akun ' . strtolower(self::ROLES[$role]) . ' berhasil ditambahkan.');
    }

    // ==================== Edit (popup di halaman daftar) ====================

    public function update(Request $request, User $user, R2StorageService $r2)
    {
        abort_unless(isset(self::ROLES[$user->role]), 404);

        $data = $this->validated($request, $user);
        unset($data['avatar']);

        // Password kosong = tidak diubah
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Admin tidak bisa menonaktifkan akunnya sendiri
        $data['is_active'] = $user->id === auth()->id() ? true : $request->boolean('is_active');

        try {
            if ($request->hasFile('avatar')) {
                $newUrl = $r2->upload($request->file('avatar'), 'avatars', R2StorageService::DISK_PUBLIC);
                $this->hapusFoto($r2, $user->avatar_url);
                $data['avatar_url'] = $newUrl;
            } elseif ($request->boolean('hapus_foto')) {
                $this->hapusFoto($r2, $user->avatar_url);
                $data['avatar_url'] = null;
            }
        } catch (\Throwable $e) {
            Log::error('Gagal memproses avatar (edit akun): ' . $e->getMessage());
            return back()->withInput()->with('error', 'Foto gagal diunggah. Coba lagi beberapa saat.');
        }

        $user->update($data);

        return redirect()
            ->route('admin.akun.' . $user->role)
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    // ==================== Status & Hapus ====================

    public function toggleStatus(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menonaktifkan akun sendiri.');

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function destroy(User $user, R2StorageService $r2)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        $avatar = $user->avatar_url;

        try {
            $user->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Akun tidak bisa dihapus karena masih punya data terkait (misalnya riwayat order). Nonaktifkan akun ini sebagai gantinya.');
        }

        $this->hapusFoto($r2, $avatar);

        return back()->with('success', 'Akun berhasil dihapus.');
    }

    // ==================== Ubah Status Availability Designer ====================

    public function updateAvailability(Request $request, User $user)
    {
        abort_unless($user->role === 'designer', 404, 'Hanya designer yang punya status availability.');

        $data = $request->validate([
            'availability_status' => ['required', 'in:available,unavailable,suspended'],
        ]);

        $stat = $user->designerStat;

        if (! $stat) {
            $stat = $user->designerStat()->create([
                'availability_status' => DesignerStat::STATUS_UNAVAILABLE,
            ]);
        }

        $payload = ['availability_status' => $data['availability_status']];

        if ($data['availability_status'] === DesignerStat::STATUS_SUSPENDED) {
            $payload['suspended_until']   = now()->addDays(DesignerStat::SUSPEND_DURATION_DAYS);
            $payload['last_suspended_at'] = now();
            $payload['suspend_count']     = $stat->suspend_count + 1;
        } elseif (in_array($data['availability_status'], [
            DesignerStat::STATUS_AVAILABLE,
            DesignerStat::STATUS_UNAVAILABLE,
        ])) {
            $payload['suspended_until'] = null;
        }

        $stat->update($payload);

        $label = match ($data['availability_status']) {
            'available'   => 'Available',
            'unavailable' => 'Unavailable',
            'suspended'   => 'Suspended',
        };

        return back()->with('success', "Status designer diubah menjadi {$label}.");
    }

    private function hapusFoto(R2StorageService $r2, ?string $url): void
    {
        if (! $url) {
            return;
        }

        try {
            $r2->delete($url, R2StorageService::DISK_PUBLIC);
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus avatar di R2: ' . $e->getMessage());
        }
    }

    // ==================== Validasi ====================

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'username'  => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user?->id)],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone'     => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'password'  => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'avatar'    => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'required'        => ':attribute wajib diisi.',
            'max'             => ':attribute maksimal :max karakter.',
            'email'           => 'Format email tidak valid.',
            'unique'          => ':attribute sudah dipakai akun lain.',
            'alpha_dash'      => 'Username hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'regex'           => 'Nomor telepon hanya boleh berisi angka, spasi, + dan -.',
            'password.min'    => 'Password minimal :min karakter.',
            'confirmed'       => 'Konfirmasi password tidak sama.',
            'avatar.image'    => 'File harus berupa gambar.',
            'avatar.mimes'    => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'avatar.max'      => 'Ukuran foto maksimal 2 MB.',
            'avatar.uploaded' => 'Foto gagal diunggah. Ukuran maksimal 2 MB.',
        ], [
            'full_name' => 'Nama lengkap',
            'username'  => 'Username',
            'email'     => 'Email',
            'phone'     => 'Nomor telepon',
            'password'  => 'Password',
            'avatar'    => 'Foto',
        ]);
    }
}