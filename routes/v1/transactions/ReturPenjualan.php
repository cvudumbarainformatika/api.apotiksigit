<?php

use App\Http\Controllers\Api\Transactions\ReturPenjualanController;
use Illuminate\Support\Facades\Route;


Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/returpenjualan'
], function () {
    // cari penjualan
    Route::get('/get-penjualan', [ReturPenjualanController::class, 'getTransaksiPenjualan']);

    // List Retur Penjualan
    Route::get('/get-list', [ReturPenjualanController::class, 'index']);

    // Update Or Create Retur Penjualan
    Route::post('/simpan', [ReturPenjualanController::class, 'simpan']);

    // Lock And Unlock
    Route::post('/lock-retur-penjualan', [ReturPenjualanController::class, 'lock_retur_penjualan']);
    // Route::post('/unlock-retur-penjualan', [ReturPenjualanController::class, 'open_lock_retur_penjualan']);

    // Hapus Retur Penjualan
    Route::post('/delete', [ReturPenjualanController::class, 'hapus']);
    Route::post('/delete-rinci', [ReturPenjualanController::class, 'hapus_rincian_tidak_dikunci']);
});
