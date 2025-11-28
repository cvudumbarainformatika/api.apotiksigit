<?php

use App\Http\Controllers\Api\Laporan\LaporanFastMovingController;
use App\Http\Controllers\Api\Laporan\LaporanHutangController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
//   'middleware' => 'auth:sanctum',
  'prefix' => 'laporan/laporan-fastmoving'
], function () {
  Route::get('/get-list', [LaporanFastMovingController::class, 'laporanfastMoving']);
});
