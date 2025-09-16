<?php

use App\Http\Controllers\Api\Setting\SubmenuController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'setting/submenu'
], function () {
  Route::get('/get-list', [SubmenuController::class, 'index']);
  Route::post('/simpan', [SubmenuController::class, 'store']);
  Route::post('/delete', [SubmenuController::class, 'hapus']);
});
