<?php

use App\Http\Controllers\Api\Transactions\BebanController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/beban'
], function () {
    Route::get('/get-list', [BebanController::class, 'index']);
    Route::post('/simpan', [BebanController::class, 'simpan']);
    Route::post('/lock_beban', [BebanController::class, 'lock_beban']);
    Route::post('/delete', [BebanController::class, 'hapus']);
});
