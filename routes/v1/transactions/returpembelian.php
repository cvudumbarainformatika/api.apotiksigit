<?php

use App\Http\Controllers\Api\Transactions\ReturPembelianController;
use Illuminate\Support\Facades\Route;


Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/returpembelian'
], function () {

    Route::get('/get-pembelian', [ReturPembelianController::class, 'getpenerimaan']);
    Route::get('/get-list', [ReturPembelianController::class, 'index']);
    Route::post('/simpan', [ReturPembelianController::class, 'simpan']);
    Route::post('/lock-retur-lock_retur_pembelian', [ReturPembelianController::class, 'lock_retur_pembelian']);
    Route::post('/delete', [ReturPembelianController::class, 'hapus']);
});
