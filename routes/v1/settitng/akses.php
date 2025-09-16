<?php

use App\Http\Controllers\Api\Setting\HakAksesController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'setting/hak-akses'
], function () {
  Route::post('/get-user', [HakAksesController::class, 'index']);
  Route::post('/grant', [HakAksesController::class, 'grant']);
  Route::post('/revoke', [HakAksesController::class, 'revoke']);
});
