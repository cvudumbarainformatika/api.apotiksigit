<?php

use App\Http\Controllers\Api\Transactions\PembayaranSetoranController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'transactions/pembayaran-setoran'
], function () {
  Route::get('/get-list', [PembayaranSetoranController::class, 'index']);
  Route::get('/get-panjualan', [PembayaranSetoranController::class, 'getPenjualan']);
  Route::post('/simpan', [PembayaranSetoranController::class, 'simpan']);
  Route::post('/kunci', [PembayaranSetoranController::class, 'kunci']);
  Route::post('/delete', [PembayaranSetoranController::class, 'hapus']);
});
