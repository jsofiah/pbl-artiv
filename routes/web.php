<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Customer\BerandaController;
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


// ============ CUSTOMER (bisa diakses guest) ============
Route::prefix('customer')->name('customer.')->group(function () {
    // Halaman publik
    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
    Route::get('/katalog', [BerandaController::class, 'katalog'])->name('katalog');

    // Halaman wajib login + role customer
    Route::middleware(['auth', 'role:customer'])->group(function () {
        Route::get('/pesanan', [BerandaController::class, 'pesanan'])->name('pesanan');
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