<?php

use App\Http\Controllers\Api\Transactions\StokOpnameController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'transactions/opname'
], function () {
  Route::get('/get-list', [StokOpnameController::class, 'index']);
  Route::post('/simpan', [StokOpnameController::class, 'simpan']);
});
