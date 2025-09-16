<?php

use App\Http\Controllers\Api\Master\BebanController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'master/beban'
], function () {
    Route::get('/get-list', [BebanController::class, 'index']);
    Route::post('/simpan', [BebanController::class, 'store']);
    Route::post('/delete', [BebanController::class, 'hapus']);
});
