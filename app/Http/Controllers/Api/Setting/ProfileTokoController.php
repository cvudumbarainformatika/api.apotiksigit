<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting\ProfileToko;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileTokoController extends Controller
{
    //
    public function index()
    {
        $data = ProfileToko::first();

        return new JsonResponse(['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'alamat' => 'nullable',
            'telepon' => 'nullable',
            'pemilik' => 'nullable',
            'header' => 'nullable',
            'footer' => 'nullable',
            'pajak' => 'nullable',
            'foto' => 'nullable',
        ], [
            'nama.required' => 'Nama wajib diisi.'
        ]);
        $data = ProfileToko::first();
        if (!$data) $data = ProfileToko::create($validated);
        else $data = ProfileToko::updateOrCreate(['id' => $data->id], $validated);

        return new JsonResponse([
            'message' => 'Data sudah di simpan',
            'data' => $data
        ]);
    }
}
