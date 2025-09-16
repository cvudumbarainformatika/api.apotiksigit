<?php

use App\Http\Controllers\Api\Laporan\LapporanLabaRugiController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'laporan/laba-rugi'
], function () {
  Route::get('/get-report', [LapporanLabaRugiController::class, 'index']);
});
