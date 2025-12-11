<?php

use App\Http\Controllers\Api\Master\CurlMasterController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  // 'middleware' => 'auth:sanctum',
  'prefix' => 'master/barang'
], function () {
  Route::post('/terima', [CurlMasterController::class, 'terimaMaster']);
});
