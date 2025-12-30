<?php

use App\Http\Controllers\Api\Master\BarangController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'master/barang'
], function () {
    Route::get('/all', [BarangController::class, 'all']);
    Route::get('/get-list', [BarangController::class, 'index']);
    Route::post('/simpan', [BarangController::class, 'store']);
    Route::post('/delete', [BarangController::class, 'hapus']);
    Route::post('/kirim-ulang', [BarangController::class, 'reSend']);
});
