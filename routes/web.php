<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\Customer\BerandaController;
use App\Http\Controllers\Customer\PemesananController;
use App\Http\Controllers\Customer\PesananController;
use App\Http\Controllers\Designer\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('customer.beranda');
    }

    return match (Auth::user()->role) {
        'admin'    => redirect()->route('admin.dashboard'),
        'designer' => redirect()->route('designer.dashboard'),
        default    => redirect()->route('customer.beranda'),
    };
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);



// ============ CUSTOMER (bisa diakses guest) ============
Route::prefix('customer')->name('customer.')->group(function () {
    // Halaman publik
    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
    Route::get('/katalog', [BerandaController::class, 'katalog'])->name('katalog');
    Route::get('/katalog/{product}', [BerandaController::class, 'detailKatalog'])->name('katalog.detail');

    // Halaman wajib login + role customer
    Route::middleware(['auth', 'role:customer'])->group(function () {
        Route::get('/pesanan', [BerandaController::class, 'pesanan'])->name('pesanan');
        Route::get('/pesanan/{order}', [PesananController::class, 'show'])->name('pesanan.show');
        Route::get('/pesanan/{order}', [PesananController::class, 'show'])->name('pesanan.show');
        Route::post('/pesanan/{order}/pesan', [PesananController::class, 'kirimPesan'])->name('pesanan.kirimPesan');

        Route::get('/pesanan/{order}/reference/{reference}/download',
            [PesananController::class, 'downloadReference'])
            ->name('pesanan.reference.download');

        Route::get('/pesanan/{order}/attachment/{attachment}/download',
            [PesananController::class, 'downloadAttachment'])
            ->name('pesanan.attachment.download');
        Route::get('/pesanan/{order}/reference/{reference}/preview',
            [PesananController::class, 'previewReference'])
            ->name('pesanan.reference.preview');
        Route::get('/pesanan/{order}/attachment/{attachment}/preview',
            [PesananController::class, 'previewAttachment'])
            ->name('pesanan.attachment.preview');

        Route::post('/pesanan/{order}/deliverable/{deliverable}/approve',
            [PesananController::class, 'approveDeliverable'])
            ->name('pesanan.deliverable.approve');
        Route::post('/pesanan/{order}/deliverable/{deliverable}/revisi',
            [PesananController::class, 'requestRevision'])
            ->name('pesanan.deliverable.revisi');

            
        Route::get('/pemesanan/{product}', [PemesananController::class, 'create'])->name('pemesanan.create');
        Route::post('/pemesanan/{product}/ringkasan', [PemesananController::class, 'ringkasan'])->name('pemesanan.ringkasan');
        Route::get('/pemesanan/{product}/ringkasan', [PemesananController::class, 'showRingkasan'])->name('pemesanan.ringkasan.show');
        Route::post('/pemesanan/{product}/konfirmasi', [PemesananController::class, 'konfirmasi'])->name('pemesanan.konfirmasi');
        Route::post('/pemesanan/{product}/hapus-referensi', [PemesananController::class, 'hapusReferensi'])
        ->name('pemesanan.hapus-referensi');
    });
});

// ============ ROUTE WAJIB LOGIN ============
Route::middleware('auth')->group(function () {

    // Profile (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Designer
    Route::middleware('role:designer')->prefix('designer')->name('designer.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/job-pool', [DashboardController::class, 'jobPool'])->name('job-pool');
        Route::get('/pekerjaan-saya', [DashboardController::class, 'pekerjaanSaya'])->name('pekerjaan-saya');
        Route::get('/notifikasi', [DashboardController::class, 'notifikasi'])->name('notifikasi');
        Route::get('/riwayat', [DashboardController::class, 'riwayat'])->name('riwayat');
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/akun/designer', [AdminDashboardController::class, 'akunDesigner'])->name('akun.designer');
        Route::get('/akun/admin', [AdminDashboardController::class, 'akunAdmin'])->name('akun.admin');
        Route::get('/akun/customer', [AdminDashboardController::class, 'akunCustomer'])->name('akun.customer');
        Route::get('/katalog', [AdminDashboardController::class, 'katalog'])->name('katalog');
        Route::get('/harga-express', [AdminDashboardController::class, 'hargaExpress'])->name('harga-express');
        Route::get('/file', [AdminDashboardController::class, 'file'])->name('file');
        Route::get('/monitoring', [AdminDashboardController::class, 'monitoring'])->name('monitoring');
        Route::get('/laporan', [AdminDashboardController::class, 'laporan'])->name('laporan');
        Route::get('/pengaturan', [AdminDashboardController::class, 'pengaturan'])->name('pengaturan');
    });
});

require __DIR__.'/auth.php';
