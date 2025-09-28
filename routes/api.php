<?php

use App\Helpers\Routes\RouteHelper;
use App\Models\Master\Barang;
use App\Models\OldApp\Master\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    RouteHelper::includeRouteFiles(__DIR__ . '/v1');
});
Route::get('/cek', function () {
    $barang = Barang::limit(3)->get();
    $prod = Product::limit(3)->get();
    return [
        'prod' => $prod,
        'barang' => $barang,
    ];
});
