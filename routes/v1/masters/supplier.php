<?php

use App\Http\Controllers\Api\Master\SupplierController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'master/supplier'
], function () {
    Route::get('/get-list', [SupplierController::class, 'index']);
    Route::post('/simpan', [SupplierController::class, 'store']);
    Route::post('/delete', [SupplierController::class, 'hapus']);
});
