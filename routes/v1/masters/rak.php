<?php

use App\Http\Controllers\Api\Master\RakController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'master/rak'
], function () {
  Route::get('/get-list', [RakController::class, 'index']);
  Route::post('/simpan', [RakController::class, 'store']);
  Route::post('/delete', [RakController::class, 'hapus']);
});
