<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — TOKO MARKETPLACE MARKETPLACE APPLICATION
|--------------------------------------------------------------------------
| Dokumen Referensi: Engineering Standards & API Contract v1.0.0
*/

// ==========================================
// ROUTE AUTENTIKASI (PUBLIC / TANPA TOKEN)
// ==========================================
// Fitur 4.1 - Login User -> POST /api/auth/login [cite: 134]
Route::post('/auth/login', [AuthController::class, 'login']);


// ==========================================
// ROUTE TERPROTEKSI (WAJIB MEMBAWA TOKEN SANCTUM)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- AUTHENTICATION SESSION ---
    // Fitur 4.3 - Verifikasi Session -> GET /api/auth/me [cite: 147]
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // Fitur 4.2 - Logout Akun -> POST /api/auth/logout [cite: 142]
    Route::post('/auth/logout', [AuthController::class, 'logout']);


    // --- DASHBOARD SYSTEM ---
    // Fitur 9.2 - Dashboard Summary -> GET /api/dashboard [cite: 232]
    Route::get('/dashboard', [ReportController::class, 'dashboardSummary']);


    // --- MANAJEMEN PRODUK (FITUR 5) ---
    // Fitur 5.1 - List Semua Produk -> GET /api/produk [cite: 154]
    Route::get('/produk', [ProductController::class, 'index']);
    
    // Fitur 5.2 - Detail Produk Tunggal -> GET /api/produk/{id} [cite: 160]
    Route::get('/produk/{id}', [ProductController::class, 'show']);
    
    // Fitur 5.3 - Tambah Produk Baru (Admin Only) -> POST /api/produk [cite: 164]
    Route::post('/produk', [ProductController::class, 'store']);
    
    // Fitur 5.4 - Edit Data Produk (Admin Only) -> PUT /api/produk/{id} [cite: 172]
    Route::put('/produk/{id}', [ProductController::class, 'update']);
    
    // Fitur 5.5 - Hapus Produk / Soft Delete (Admin Only) -> DELETE /api/produk/{id} [cite: 175, 176]
    Route::delete('/produk/{id}', [ProductController::class, 'destroy']);


    // --- MANAJEMEN PESANAN (FITUR 6) ---
    // Fitur 6.1 - List Semua Pesanan -> GET /api/pesanan [cite: 182]
    Route::get('/pesanan', [OrderController::class, 'index']);
    
    // Fitur 6.2 - Tambah Pesanan Baru & Auto Potong Stok -> POST /api/pesanan [cite: 188, 191]
    Route::post('/pesanan', [OrderController::class, 'store']);
    
    // Fitur 6.3 - Update Status Pesanan -> PATCH /api/pesanan/{id}/status [cite: 195]
    Route::patch('/pesanan/{id}/status', [OrderController::class, 'updateStatus']);


    // --- MANAJEMEN STOK (FITUR 7) ---
    // Fitur 7.1 - Riwayat Pergerakan Stok -> GET /api/produk/{id}/stok [cite: 201]
    Route::get('/produk/{id}/stok', [StockController::class, 'index']);
    
    // Fitur 7.2 - Tambah Stok / Restock Manual -> POST /api/produk/{id}/stok/masuk [cite: 205]
    Route::post('/produk/{id}/stok/masuk', [StockController::class, 'storeMasuk']);


    // --- LAPORAN KEUANGAN (FITUR 9) ---
    // Fitur 9.1 - Laporan Penjualan (Admin Only) -> GET /api/laporan/penjualan [cite: 226]
    Route::get('/laporan/penjualan', [ReportController::class, 'salesReport']);

});