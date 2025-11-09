<?php

use App\Http\Controllers\DataMigration\CekDataController;
use App\Models\Master\Barang;
use App\Models\OldApp\Master\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/autogen', function () {
    $user = User::limit(10)->get();
    return $user;
});

// Route::get('/cek', [CekDataController::class, 'index']);
Route::get('/cek', function () {
    $barang = Barang::limit(3)->get();
    $prod = Product::limit(3)->get();
    return [
        'prod' => $prod,
        'barang' => $barang,
    ];
});
Route::get('/test-curl', function () {
    try {
        $response = Http::timeout(10)->post('http://api.apotiksigit.test/api/v1/transactions/curl-mutasi/terima-curl');
        return $response->json();
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
