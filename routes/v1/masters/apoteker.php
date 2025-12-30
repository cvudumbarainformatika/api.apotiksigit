<?php

use App\Http\Controllers\Api\Master\ApotekerController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'master/apoteker'
], function () {
  Route::get('/get-list', [ApotekerController::class, 'index']);
  Route::post('/simpan', [ApotekerController::class, 'store']);
  Route::post('/delete', [ApotekerController::class, 'hapus']);
});
