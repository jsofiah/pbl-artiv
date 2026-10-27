<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\Customer\BerandaController;
use App\Http\Controllers\Customer\PemesananController;
use App\Http\Controllers\Customer\PembayaranController;
use App\Http\Controllers\Customer\PesananController;
use App\Http\Controllers\Designer\DashboardController;
use App\Http\Controllers\Designer\JobPoolController;
use App\Http\Controllers\Designer\PekerjaanController;
use App\Http\Controllers\Designer\RiwayatPekerjaanController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\KatalogController;
use App\Http\Controllers\Admin\ProductTierController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PengaturanController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\Customer\EnsureOrderCanBePaid;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Admin\ManajemenFileController;

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
        Route::post('/pesanan/{order}/pesan', [PesananController::class, 'kirimPesan'])->name('pesanan.kirimPesan');

        Route::get(
            '/pesanan/{order}/reference/{reference}/download',
            [PesananController::class, 'downloadReference']
        )
            ->name('pesanan.reference.download');

        Route::get(
            '/pesanan/{order}/attachment/{attachment}/download',
            [PesananController::class, 'downloadAttachment']
        )
            ->name('pesanan.attachment.download');
        Route::get(
            '/pesanan/{order}/reference/{reference}/preview',
            [PesananController::class, 'previewReference']
        )
            ->name('pesanan.reference.preview');
        Route::get(
            '/pesanan/{order}/attachment/{attachment}/preview',
            [PesananController::class, 'previewAttachment']
        )
            ->name('pesanan.attachment.preview');

        Route::post(
            '/pesanan/{order}/deliverable/{deliverable}/approve',
            [PesananController::class, 'approveDeliverable']
        )
            ->name('pesanan.deliverable.approve');
        Route::post(
            '/pesanan/{order}/deliverable/{deliverable}/revisi',
            [PesananController::class, 'requestRevision']
        )
            ->name('pesanan.deliverable.revisi');


        Route::get('/pemesanan/{product}', [PemesananController::class, 'create'])->name('pemesanan.create');
        Route::post('/pemesanan/{product}/ringkasan', [PemesananController::class, 'ringkasan'])->name('pemesanan.ringkasan');
        Route::get('/pemesanan/{product}/ringkasan', [PemesananController::class, 'showRingkasan'])->name('pemesanan.ringkasan.show');
        Route::post('/pemesanan/{product}/konfirmasi', [PemesananController::class, 'konfirmasi'])->name('pemesanan.konfirmasi');
        Route::post('/pemesanan/{product}/hapus-referensi', [PemesananController::class, 'hapusReferensi'])->name('pemesanan.hapus-referensi');
        Route::get('/pemesanan/{order}/pembayaran', [PembayaranController::class, 'showByOrder'])
            ->middleware(EnsureOrderCanBePaid::class)
            ->name('pemesanan.pembayaran');

        Route::post('/pemesanan/{order}/pembayaran', [PembayaranController::class, 'store'])
            ->middleware(EnsureOrderCanBePaid::class)
            ->name('pemesanan.pembayaran.store');

        Route::get('/pemesanan/{order}/pembayaran/sukses', [PembayaranController::class, 'success'])
            ->name('pemesanan.sukses');

        // Review & rating
        Route::prefix('/pesanan/{order}/review')->name('review.')->group(function () {
            Route::get('/', [ReviewController::class, 'create'])->name('create');
            Route::post('/', [ReviewController::class, 'store'])->name('store');
            Route::get('/sukses', [ReviewController::class, 'sukses'])->name('sukses');
        });

        // Notifikasi
        Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifikasi.read-all');
        Route::get('/notifikasi/{notification}', [NotificationController::class, 'read'])->name('notifikasi.read');
    });
});


// ============ ROUTE WAJIB LOGIN ============
Route::middleware('auth')->group(function () {

    // Profile (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/designer-status', [ProfileController::class, 'updateDesignerStatus'])
        ->middleware('role:designer')
        ->name('profile.designer-status.update');

    // Designer
    Route::middleware('role:designer')->prefix('designer')->name('designer.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::post('/availability/toggle', [DashboardController::class, 'toggleAvailability'])
            ->name('availability.toggle');

        // ============ JOB POOL ============
        Route::get('/job-pool', [JobPoolController::class, 'index'])->name('job-pool');
        Route::get('/job-pool/{order}', [JobPoolController::class, 'show'])->name('job-pool.show');
        Route::post('/job-pool/{order}/ambil', [JobPoolController::class, 'ambil'])->name('job-pool.ambil');
        Route::get('/job-pool/{order}/sukses', [JobPoolController::class, 'sukses'])->name('job-pool.sukses');
        Route::get('/job-pool/{order}/reference/{reference}/download', [JobPoolController::class, 'downloadReference'])->name('job-pool.reference.download');
        Route::get('/job-pool/{order}/reference/{reference}/preview', [JobPoolController::class, 'previewReference'])->name('job-pool.reference.preview');
        // ============ PEKERJAAN ============
        Route::get('/pekerjaan-saya', [PekerjaanController::class, 'index'])->name('pekerjaan.index');
        Route::get('/pekerjaan-saya/{order}', [PekerjaanController::class, 'show'])->name('pekerjaan.show');
        Route::post('/pekerjaan-saya/{order}/pesan', [PekerjaanController::class, 'kirimPesan'])->name('pekerjaan.kirimPesan');

        Route::get(
            '/pekerjaan-saya/{order}/attachment/{attachment}/download',
            [PekerjaanController::class, 'downloadAttachment']
        )->name('pekerjaan.attachment.download');

        Route::get(
            '/pekerjaan-saya/{order}/reference/{reference}/download',
            [PekerjaanController::class, 'downloadReference']
        )->name('pekerjaan.reference.download');

        Route::get(
            '/pekerjaan-saya/{order}/attachment/{attachment}/preview',
            [PekerjaanController::class, 'previewAttachment']
        )->name('pekerjaan.attachment.preview');

        Route::get(
            '/pekerjaan-saya/{order}/reference/{reference}/preview',
            [PekerjaanController::class, 'previewReference']
        )->name('pekerjaan.reference.preview');

        Route::post(
            '/pekerjaan-saya/{order}/deliverable',
            [PekerjaanController::class, 'kirimDeliverable']
        )
            ->name('pekerjaan.kirimDeliverable');
        Route::get('/notifikasi', [DashboardController::class, 'notifikasi'])->name('notifikasi');
        Route::get('/riwayat', [RiwayatPekerjaanController::class, 'index'])->name('riwayat');
        Route::get('/riwayat/ekspor-csv', [RiwayatPekerjaanController::class, 'exportCsv'])->name('riwayat.csv');
        Route::get('/riwayat/cetak', [RiwayatPekerjaanController::class, 'cetak'])->name('riwayat.cetak');
        Route::get('/riwayat/{order}', [RiwayatPekerjaanController::class, 'show'])->whereUuid('order')->name('riwayat.show');
        Route::get('/riwayat/{order}/hasil/{attachment}/download', [RiwayatPekerjaanController::class, 'downloadHasil'])
            ->whereUuid(['order', 'attachment'])->name('riwayat.hasil.download');
    });


    // Admin
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard Utama & Queue Export
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

        // Monitoring (Pekerjaan & Pembayaran)
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
        Route::get('/monitoring/detail/{order}', [MonitoringController::class, 'detail'])->name('monitoring.detail');
        Route::patch('/monitoring/pembayaran/{payment}/approve', [MonitoringController::class, 'approvePayment'])->name('monitoring.pembayaran.approve');
        Route::patch('/monitoring/pembayaran/{payment}/reject', [MonitoringController::class, 'rejectPayment'])->name('monitoring.pembayaran.reject');

    // Katalog
    Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
    Route::get('/katalog/create', [KatalogController::class, 'create'])->name('katalog.create');
    Route::post('/katalog', [KatalogController::class, 'store'])->name('katalog.store');
    Route::get('/katalog/{product}/edit', [KatalogController::class, 'edit'])->name('katalog.edit');
    Route::put('/katalog/{product}', [KatalogController::class, 'update'])->name('katalog.update');
    Route::delete('/katalog/{product}', [KatalogController::class, 'destroy'])->name('katalog.destroy');

    // Tier
    Route::get('/katalog/{product}/tiers', [ProductTierController::class, 'index'])->name('katalog.tiers.index');
    Route::post('/katalog/{product}/tiers', [ProductTierController::class, 'store'])->name('katalog.tiers.store');
    Route::put('/katalog/{product}/tiers/{tier}', [ProductTierController::class, 'update'])->name('katalog.tiers.update');
    Route::delete('/katalog/{product}/tiers/{tier}', [ProductTierController::class, 'destroy'])->name('katalog.tiers.destroy');
        
        // Route Manajemen File
        Route::get('/manajemen-file', [ManajemenFileController::class, 'index'])->name('manajemen-file.index');
        Route::get('/manajemen-file/preview/{type}/{id}', [ManajemenFileController::class, 'preview'])->name('manajemen-file.preview');
        Route::delete('/manajemen-file/{type}/{id}', [ManajemenFileController::class, 'destroy'])->name('manajemen-file.destroy');
        // Modul Terpisah
            Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

    // Pengaturan
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan');
    Route::post('/pengaturan/blacklist', [PengaturanController::class, 'storeBlacklist'])->name('pengaturan.blacklist.store');
    Route::put('/pengaturan/blacklist/{keyword}', [PengaturanController::class, 'updateBlacklist'])->name('pengaturan.blacklist.update');
    Route::delete('/pengaturan/blacklist/{keyword}', [PengaturanController::class, 'destroyBlacklist'])->name('pengaturan.blacklist.destroy');
    Route::post('/pengaturan/harga-revisi', [PengaturanController::class, 'updateRevisionFee'])->name('pengaturan.revision-fee.update');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
        Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    });
});

require __DIR__ . '/auth.php';
