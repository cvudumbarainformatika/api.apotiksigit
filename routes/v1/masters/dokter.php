<?php

use App\Http\Controllers\Api\Master\DokterController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'master/dokter'
], function () {
    Route::get('/get-list', [DokterController::class, 'index']);
    Route::post('/simpan', [DokterController::class, 'store']);
    Route::post('/delete', [DokterController::class, 'hapus']);
});
