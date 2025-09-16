<?php

use App\Http\Controllers\Api\Transactions\StokController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/stok'
], function () {
    Route::get('/get-list', [StokController::class, 'index']);
    // kartu stok
    Route::get('/get-kartu-stok', [StokController::class, 'kartuStok']);
    Route::get('/get-rinci-kartu-stok', [StokController::class, 'kartuStokRinci']);
    // simpan penyesuaian stok
    Route::post('/simpan', [StokController::class, 'simpanPenyesuaian']);
});
