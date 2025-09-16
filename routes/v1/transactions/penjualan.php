<?php

use App\Http\Controllers\Api\Transactions\PenjualanController;
use Illuminate\Support\Facades\Route;


Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/penjualan'
], function () {
    // list obat pake stok
    Route::get('/get-list-obat', [PenjualanController::class, 'getListObat']);

    // List Penjualan
    Route::get('/get-list', [PenjualanController::class, 'index']);

    // tambah barang
    Route::post('/simpan', [PenjualanController::class, 'simpan']);
    Route::post('/bayar', [PenjualanController::class, 'bayar']);

    // hapus rincian data
    Route::post('/hapus', [PenjualanController::class, 'hapus']);
});;
