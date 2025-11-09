<?php

use App\Http\Controllers\Api\Transactions\MutasiController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  // 'middleware' => 'auth:sanctum',
  'prefix' => 'transactions/mutasi'
], function () {
  Route::get('/get-cabang', [MutasiController::class, 'getCabang']); // ambil cabang dari tabel
  Route::get('/get-barang', [MutasiController::class, 'getBarang']); // ambil barang dan stok
  Route::get('/get-list', [MutasiController::class, 'index']); // ambil list mutasi. bisa dipilih berdasarkan dari / tujuan.
  Route::post('/simpan', [MutasiController::class, 'simpan']); // simpan permintaan mutasi oleh depo.
  Route::post('/delete', [MutasiController::class, 'hapus']); // hapus rincian selama belum di dikirim
  Route::post('/kirim', [MutasiController::class, 'kirim']); // kirim permintaan dari depo
  Route::post('/simpan-distribusi', [MutasiController::class, 'simpanDistribusi']); // simpan jumlah barang yang di setujui oleh gudang
  Route::post('/kirim-distribusi', [MutasiController::class, 'kirimDistribusi']); // kirim balik yang sudah di isi jumlah distribusinya
  Route::post('/terima', [MutasiController::class, 'terima']); // terima barang yang di distribusi oleh gudang
});

/**
 * ini untuk curl
 */
Route::group([
  'prefix' => 'transactions/curl-mutasi'
], function () {
  Route::post('/terima-curl', [MutasiController::class, 'terimaCurl']); // terima barang yang di distribusi oleh gudang
});
