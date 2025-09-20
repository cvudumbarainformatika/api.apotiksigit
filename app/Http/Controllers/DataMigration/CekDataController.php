<?php

namespace App\Http\Controllers\DataMigration;

use App\Http\Controllers\Controller;
use App\Models\OldApp\Master\Product;
use Illuminate\Http\Request;

class CekDataController extends Controller
{
    //
    public function index()
    {
        $data = Product::limit(10)->get();
        return $data;
    }
}
