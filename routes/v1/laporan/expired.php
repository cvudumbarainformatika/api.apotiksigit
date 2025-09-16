<?php

use App\Http\Controllers\Api\Laporan\LaporanExpiredController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'laporan/kategori-expired'
], function () {
  Route::get('/get-list', [LaporanExpiredController::class, 'index']);
});
