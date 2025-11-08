<?php

use App\Http\Controllers\Api\Laporan\DasboardController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'laporan/dashboard'
], function () {
  Route::get('/fasmoving', [DasboardController::class, 'fasmoving']);
  Route::get('/toppbf', [DasboardController::class, 'toppbf']);
  Route::get('/pen-pem-pbl', [DasboardController::class, 'penjualanPembelianPerbulanTahunIni']);
  Route::get('/pen-pem-harian', [DasboardController::class, 'penjualanPembelianHarian']);
});
