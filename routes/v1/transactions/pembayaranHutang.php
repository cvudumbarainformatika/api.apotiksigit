<?php

use App\Http\Controllers\Api\Transactions\PembayaranHutangController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'transactions/pembayaran-hutang'
], function () {
  Route::get('/get-list', [PembayaranHutangController::class, 'index']);
  Route::get('/get-one-hutang', [PembayaranHutangController::class, 'getOneHutang']);
  Route::get('/get-hutang', [PembayaranHutangController::class, 'getHutang']);
  Route::post('/simpan', [PembayaranHutangController::class, 'simpan']);
  Route::post('/kunci', [PembayaranHutangController::class, 'kunci']);
  Route::post('/delete', [PembayaranHutangController::class, 'hapus']);
});
