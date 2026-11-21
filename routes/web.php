<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Halaman Publik
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
Route::post('/kiosk/ticket', [KioskController::class, 'storeTicket'])->name('kiosk.ticket');

Route::get('/display', [DisplayController::class, 'index'])->name('display.index');
Route::get('/display/stream', [DisplayController::class, 'streamUpdates'])->name('display.stream');
Route::get('/display/poll-updates', [DisplayController::class, 'pollUpdates'])->name('display.poll');

// Autentikasi Pengguna
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute yang Memerlukan Login
Route::middleware(['auth'])->group(function () {
    // Fitur Ganti Kata Sandi Sendiri
    Route::get('/password/change', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.update');

    // Rute Meja Kerja Loket Pelayanan (Bisa diakses Petugas & Admin)
    Route::middleware(['role:petugas'])->group(function () {
        Route::get('/loket', [CounterController::class, 'index'])->name('counter.index');
        Route::post('/loket/call-next', [CounterController::class, 'callNext'])->name('counter.callNext');
        Route::post('/loket/recall', [CounterController::class, 'recall'])->name('counter.recall');
        Route::post('/loket/start-serve', [CounterController::class, 'startServing'])->name('counter.startServing');
        Route::post('/loket/complete', [CounterController::class, 'complete'])->name('counter.complete');
        Route::post('/loket/skip', [CounterController::class, 'skip'])->name('counter.skip');
        Route::post('/loket/recall-skipped', [CounterController::class, 'recallSkipped'])->name('counter.recallSkipped');
    });

    // Rute Khusus Administrator
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.exportCsv');
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');

        // Kelola Layanan
        Route::get('/services', [\App\Http\Controllers\Admin\ServiceController::class, 'index'])->name('services.index');
        Route::post('/services', [\App\Http\Controllers\Admin\ServiceController::class, 'store'])->name('services.store');
        Route::put('/services/{service}', [\App\Http\Controllers\Admin\ServiceController::class, 'update'])->name('services.update');
        Route::patch('/services/{service}/toggle', [\App\Http\Controllers\Admin\ServiceController::class, 'toggleActive'])->name('services.toggle');

        // Kelola Loket
        Route::get('/counters', [\App\Http\Controllers\Admin\CounterController::class, 'index'])->name('counters.index');
        Route::post('/counters', [\App\Http\Controllers\Admin\CounterController::class, 'store'])->name('counters.store');
        Route::put('/counters/{counter}', [\App\Http\Controllers\Admin\CounterController::class, 'update'])->name('counters.update');
        Route::patch('/counters/{counter}/toggle', [\App\Http\Controllers\Admin\CounterController::class, 'toggleActive'])->name('counters.toggle');

        // Kelola Pengguna
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/toggle', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('users.toggle');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.resetPassword');
    });
});
