<?php

use App\Http\Controllers\Api\Setting\MenuController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'setting/menu'
], function () {
  Route::get('/get-list', [MenuController::class, 'index']);
  Route::post('/simpan', [MenuController::class, 'store']);
  Route::post('/delete', [MenuController::class, 'hapus']);
});
