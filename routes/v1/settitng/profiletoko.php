<?php

use App\Http\Controllers\Api\Setting\ProfileTokoController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'setting/profile-toko'
], function () {
  Route::get('/get-profile', [ProfileTokoController::class, 'index']);
  Route::post('/simpan', [ProfileTokoController::class, 'store']);
});
