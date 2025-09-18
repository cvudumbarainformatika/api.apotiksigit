<?php

use App\Http\Controllers\Api\Master\KategoriController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'master/kategori'
], function () {
  Route::get('/get-list', [KategoriController::class, 'index']);
  Route::post('/simpan', [KategoriController::class, 'store']);
  Route::post('/delete', [KategoriController::class, 'hapus']);
});
