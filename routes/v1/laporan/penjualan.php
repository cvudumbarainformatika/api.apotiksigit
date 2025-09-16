<?php

use App\Http\Controllers\Api\Laporan\LaporanPenjualanController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'laporan/penjualan'
], function () {
  Route::get('/get-barang', [LaporanPenjualanController::class, 'barang']);
  Route::get('/get-transaction', [LaporanPenjualanController::class, 'transakction']);
});
