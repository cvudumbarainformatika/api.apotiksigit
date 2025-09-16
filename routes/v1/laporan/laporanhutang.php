<?php

use App\Http\Controllers\Api\Laporan\LaporanHutangController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
//   'middleware' => 'auth:sanctum',
  'prefix' => 'laporan/laporan-hutang'
], function () {
  Route::get('/get-list', [LaporanHutangController::class, 'index']);
});
