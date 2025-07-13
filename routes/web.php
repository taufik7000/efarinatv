<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PengajuanAnggaranPrintController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirect admin login ke login terpusat
Route::get('/admin/login', function() {
    return redirect('/login');
});

// Redirect semua panel login ke login terpusat
Route::get('/direktur/login', function() {
    return redirect('/login');
});

Route::get('/hrd/login', function() {
    return redirect('/login');
});

Route::get('/keuangan/login', function() {
    return redirect('/login');
});

Route::get('/marketing/login', function() {
    return redirect('/login');
});

Route::get('/redaksi/login', function() {
    return redirect('/login');
});

Route::get('/berita', [PostController::class, 'index'])->name('posts.index');
Route::get('/berita/{slug}', [PostController::class, 'show'])->name('posts.show');

// Print Routes - Hanya untuk user yang sudah login
Route::middleware(['auth'])->group(function () {
    Route::get('/pengajuan-anggaran/{pengajuan}/print', [PengajuanAnggaranPrintController::class, 'print'])
        ->name('pengajuan.print');
    
    Route::get('/pengajuan-anggaran/{pengajuan}/export-pdf', [PengajuanAnggaranPrintController::class, 'exportPdf'])
        ->name('pengajuan.export.pdf');
});