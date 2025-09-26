<?php

use App\Http\Controllers\DataMigration\CekDataController;
use App\Models\Setting\Menu;
use App\Models\User;
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

Route::get('/cek', function () {
    $laporan = Menu::firstOrCreate(
        ['title' => 'Laporan'],
        [
            'icon' => 'layers',
            'url' => 'admin/laporan',
            'name' => null,
            'view' => null,
            'component' => null,
        ]
    );
    $laporan->load('children');
    return $laporan;
});
