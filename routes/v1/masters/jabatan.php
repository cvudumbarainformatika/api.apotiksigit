<?php

use App\Http\Controllers\Api\Master\JabatanController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'master/jabatan'
], function () {
    Route::get('/get-list', [JabatanController::class, 'index']);
    Route::post('/simpan', [JabatanController::class, 'store']);
    Route::post('/delete', [JabatanController::class, 'hapus']);
});
