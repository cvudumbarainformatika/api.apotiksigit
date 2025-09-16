<?php

use App\Http\Controllers\Api\Master\KetegoriExpiredController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'master/kategori-expired'
], function () {
  Route::get('/get-list', [KetegoriExpiredController::class, 'index']);
  Route::post('/simpan', [KetegoriExpiredController::class, 'store']);
  Route::post('/delete', [KetegoriExpiredController::class, 'hapus']);
});
