<?php

use App\Http\Controllers\Api\Transactions\PenerimaanController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/penerimaan'
], function () {
    Route::get('/get-list', [PenerimaanController::class, 'index']);
    Route::post('/simpan', [PenerimaanController::class, 'simpan']);
    Route::post('/lock_penerimaan', [PenerimaanController::class, 'lock_penerimaan']);
    Route::post('/delete', [PenerimaanController::class, 'hapus']);
});
