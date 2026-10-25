<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
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
            $query->addSelect('users.*')->addSelect([
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

        $akun = $query->paginate(6)->withQueryString();

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

    // ==================== Tambah ====================

    public function create(string $role)
    {
        abort_unless(isset(self::ROLES[$role]), 404);

        return view('admin.akun.form', [
            'role'      => $role,
            'roleLabel' => self::ROLES[$role],
            'user'      => null,
        ]);
    }

    public function store(Request $request, string $role)
    {
        abort_unless(isset(self::ROLES[$role]), 404);

        $data = $this->validated($request);

        User::create($data + [
            'role'      => $role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.akun.' . $role)
            ->with('success', 'Akun ' . strtolower(self::ROLES[$role]) . ' berhasil ditambahkan.');
    }

    // ==================== Edit ====================

    public function edit(User $user)
    {
        abort_unless(isset(self::ROLES[$user->role]), 404);

        return view('admin.akun.form', [
            'role'      => $user->role,
            'roleLabel' => self::ROLES[$user->role],
            'user'      => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        // Password kosong = tidak diubah
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Admin tidak bisa menonaktifkan akunnya sendiri
        $data['is_active'] = $user->id === auth()->id() ? true : $request->boolean('is_active');

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

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        try {
            $user->delete();
        } catch (QueryException $e) {
            // Mis. customer yang masih punya order (FK restrict)
            return back()->with('error', 'Akun tidak bisa dihapus karena masih punya data terkait (misalnya riwayat order). Nonaktifkan akun ini sebagai gantinya.');
        }

        return back()->with('success', 'Akun berhasil dihapus.');
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
        ], [
            'required'        => ':attribute wajib diisi.',
            'max'             => ':attribute maksimal :max karakter.',
            'email'           => 'Format email tidak valid.',
            'unique'          => ':attribute sudah dipakai akun lain.',
            'alpha_dash'      => 'Username hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'regex'           => 'Nomor telepon hanya boleh berisi angka, spasi, + dan -.',
            'password.min'    => 'Password minimal :min karakter.',
            'confirmed'       => 'Konfirmasi password tidak sama.',
        ], [
            'full_name' => 'Nama lengkap',
            'username'  => 'Username',
            'email'     => 'Email',
            'phone'     => 'Nomor telepon',
            'password'  => 'Password',
        ]);
    }
}