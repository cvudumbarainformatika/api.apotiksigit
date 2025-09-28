<?php

use App\Http\Controllers\Api\Transactions\MutasiController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'transactions/mutasi'
], function () {
  Route::get('/get-cabang', [MutasiController::class, 'getCabang']);
  Route::get('/get-barang', [MutasiController::class, 'getBarang']);
  Route::get('/get-list', [MutasiController::class, 'index']);
  Route::post('/simpan', [MutasiController::class, 'simpan']);
  Route::post('/delete', [MutasiController::class, 'hapus']);
  Route::post('/kirim', [MutasiController::class, 'kirim']);
  Route::post('/simpan-distribusi', [MutasiController::class, 'simpanDistribusi']);
  Route::post('/kirim-distribusi', [MutasiController::class, 'kirimDistribusi']);
  Route::post('/terima', [MutasiController::class, 'terima']);
});
