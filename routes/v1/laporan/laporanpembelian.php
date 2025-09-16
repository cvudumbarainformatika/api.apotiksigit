<?php

use App\Http\Controllers\Api\Laporan\LaporanPembelianController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'laporan/laporan-pembelian'
], function () {
  Route::get('/get-list', [LaporanPembelianController::class, 'index']);
});
