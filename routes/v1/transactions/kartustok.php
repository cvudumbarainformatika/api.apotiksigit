<?php

use App\Http\Controllers\Api\Transactions\KartuStokController;
use App\Http\Controllers\Api\Transactions\PenyesuaianController;
use Illuminate\Support\Facades\Route;


Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/kartustok'
], function () {
    // list barang dengan transaksi
    Route::get('/get-list-obat', [KartuStokController::class, 'getListObat']);

    // Simpan Penyesuaian
    Route::get('/simpan', [PenyesuaianController::class, 'simpan']);
});;
