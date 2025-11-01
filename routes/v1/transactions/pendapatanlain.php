<?php

use App\Http\Controllers\Api\Transactions\PendapatanlainController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/pendapatanlain'
], function () {
    Route::get('/get-list', [PendapatanlainController::class, 'index']);
    Route::post('/simpan', [PendapatanlainController::class, 'store']);
    Route::post('/kunci', [PendapatanlainController::class, 'lock']);
    Route::post('/hapus', [PendapatanlainController::class, 'delete']);
});
