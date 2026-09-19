<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\MasterTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubDataController;
use Illuminate\Support\Facades\Route;

// Guest
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Notifikasi — semua role
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Dokumen Digital — semua role (user: dokumen sendiri; admin: semua + verifikasi)
    Route::get('/dokumen', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/dokumen/upload', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/dokumen', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/dokumen/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/dokumen/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Verifikasi dokumen — hanya Admin/Super Admin
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/pegawai/{employee}/dokumen/upload', [DocumentController::class, 'create'])->name('documents.create-admin');
        Route::get('/dokumen/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
        Route::put('/dokumen/{document}', [DocumentController::class, 'update'])->name('documents.update');
        Route::post('/dokumen/{document}/verify', [DocumentController::class, 'verify'])->name('documents.verify');
        Route::post('/dokumen/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
    });

    // Sub-data pegawai (Pendidikan, Diklat, Jabatan, Pangkat, Mutasi, Kinerja, Penghargaan, Keluarga)
    // Semua role login bisa akses data milik sendiri (otorisasi kepemilikan di SubDataController);
    // Admin/Super Admin akses semua employee.
    Route::get('/pegawai/{employee}/sub/{type}/create', [SubDataController::class, 'create'])->name('sub.create');
    Route::post('/pegawai/{employee}/sub/{type}', [SubDataController::class, 'store'])->name('sub.store');
    Route::get('/pegawai/{employee}/sub/{type}/{id}/edit', [SubDataController::class, 'edit'])->name('sub.edit');
    Route::put('/pegawai/{employee}/sub/{type}/{id}', [SubDataController::class, 'update'])->name('sub.update');
    Route::delete('/pegawai/{employee}/sub/{type}/{id}', [SubDataController::class, 'destroy'])->name('sub.destroy');
    Route::get('/sub/{type}/{id}/download', [SubDataController::class, 'download'])->name('sub.download');

    // Profil Saya / Pengaturan akun sendiri — semua role
    Route::get('/profil-saya', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil-saya', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profil-saya/foto', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::delete('/profil-saya/foto', [ProfileController::class, 'deletePhoto'])->name('profile.photo-delete');
    Route::get('/pengaturan/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/pengaturan/password', [PasswordController::class, 'update'])->name('password.update');

    // Pengaturan sistem — hanya Super Admin
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/pengaturan/sistem', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan/sistem', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/pengaturan/sistem/foto-hapus', [SettingsController::class, 'deletePhoto'])->name('settings.photo-delete');
    });

    // Laporan — Super Admin & Admin
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/{jenis}/csv', [ReportController::class, 'csv'])->name('reports.csv');
    });

    // Lihat Data Pegawai — hanya Admin/Super Admin (role user hanya lihat profil sendiri via Policy)
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/pegawai', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/penelusuran', [EmployeeController::class, 'searchJson'])->name('employees.search');
    });

    // Kelola Data Pegawai — Super Admin & Admin (harus sebelum route {employee} agar /tambah & /export tidak tertelan)
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/pegawai/export', [EmployeeController::class, 'exportCsv'])->name('employees.export');
        Route::get('/pegawai/tambah', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/pegawai', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/pegawai/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/pegawai/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::post('/pegawai/{employee}/foto', [EmployeeController::class, 'updatePhoto'])->name('employees.photo');
    });

    // Detail pegawai — Admin/Super Admin semua; role user hanya data dirinya sendiri (Policy view)
    Route::get('/pegawai/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

    // Modul Master Data — Super Admin & Admin
    Route::middleware('role:super_admin,admin')->prefix('master')->name('master.')->group(function () {
        // Kelola definisi jenis master data (harus sebelum /{type} agar tidak tertelan)
        Route::get('/tipe', [MasterTypeController::class, 'index'])->name('types.index');
        Route::get('/tipe/create', [MasterTypeController::class, 'create'])->name('types.create');
        Route::post('/tipe', [MasterTypeController::class, 'store'])->name('types.store');
        Route::get('/tipe/{masterType}/edit', [MasterTypeController::class, 'edit'])->name('types.edit');
        Route::put('/tipe/{masterType}', [MasterTypeController::class, 'update'])->name('types.update');
        Route::delete('/tipe/{masterType}', [MasterTypeController::class, 'destroy'])->name('types.destroy');

        Route::get('/{type}', [MasterDataController::class, 'index'])->name('index');
        Route::get('/{type}/create', [MasterDataController::class, 'create'])->name('create');
        Route::post('/{type}', [MasterDataController::class, 'store'])->name('store');
        Route::get('/{type}/{id}/edit', [MasterDataController::class, 'edit'])->name('edit');
        Route::put('/{type}/{id}', [MasterDataController::class, 'update'])->name('update');
        Route::delete('/{type}/{id}', [MasterDataController::class, 'destroy'])->name('destroy');
    });

    // Modul Approval — hanya Super Admin yang bisa approve/reject, Admin bisa lihat
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/approval', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approval/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approval/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approval/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // Modul Manajemen Akun — HANYA Super Admin
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::patch('/accounts/{account}/toggle-active', [AccountController::class, 'toggleActive'])->name('accounts.toggle-active');
        Route::patch('/accounts/{account}/reset-password', [AccountController::class, 'resetPassword'])->name('accounts.reset-password');
        Route::get('/accounts/{account}/password', [AccountController::class, 'revealPassword'])->name('accounts.password');
        Route::patch('/accounts/{account}/role', [AccountController::class, 'updateRole'])->name('accounts.update-role');
        Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    });

    // Audit Log — Super Admin & Admin
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
    });
});

Route::get('/', fn () => redirect()->route('login'));