<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\KategoriExpired;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KetegoriExpiredController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];
        $raw = KategoriExpired::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function store(Request $request)
    {
        $kode = $request->kode;
        $validated = $request->validate([
            'nama' => 'required',
            'dari' => 'required|numeric',
            'sampai' => 'required|numeric',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'dari.required' => 'Dari wajib diisi.',
            'dari.numeric' => 'Dari harus Angka.',
            'sampai.required' => 'Sampai wajib diisi.',
            'sampai.numeric' => 'Sampai harus Angka.'
        ]);

        if (!$kode) {
            DB::select('call kode_kategori(@nomor)');
            $nomor = DB::table('counter')->select('kode_kategori')->first();
            $kode = FormatingHelper::genKodeBarang($nomor->kode_kategori, 'EXP');
        }

        $barang = KategoriExpired::updateOrCreate(
            [
                'kode' =>  $kode
            ],
            $validated
        );
        return new JsonResponse([
            'data' => $barang,
            'message' => 'Data barang berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $barang = KategoriExpired::find($request->id);
        if (!$barang) {
            return new JsonResponse([
                'message' => 'Data barang tidak ditemukan'
            ], 410);
        }
        $barang->update(['hidden' => '1']);
        return new JsonResponse([
            'data' => $barang,
            'message' => 'Data barang berhasil dihapus'
        ]);
    }
}
