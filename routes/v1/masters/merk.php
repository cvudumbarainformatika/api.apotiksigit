<?php

use App\Http\Controllers\Api\Master\MerkController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'master/merk'
], function () {
    Route::get('/get-list', [MerkController::class, 'index']);
    Route::post('/simpan', [MerkController::class, 'store']);
    Route::post('/delete', [MerkController::class, 'hapus']);
    Route::post('/kirim-ulang', [MerkController::class, 'reSend']);
});
