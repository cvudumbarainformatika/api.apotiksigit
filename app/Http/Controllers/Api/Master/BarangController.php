<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    //
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];
        $raw = Barang::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->whereNull('hidden')
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
            'satuan_k' => 'nullable',
            'satuan_b' => 'nullable',
            'isi' => 'nullable',
            'merk' => 'nullable',
            'rak' => 'nullable',
            'kategori' => 'nullable',
            'kandungan' => 'nullable',
            'harga_beli' => 'nullable',
            'harga_jual_umum' => 'nullable',
            'harga_jual_resep' => 'nullable',
            'harga_jual_cust' => 'nullable',
            'harga_jual_prem' => 'nullable',
            'limit_stok' => 'nullable',
            // disediakan untuk jaga2
            'margin_jual_umum' => 'nullable',
            'margin_jual_resep' => 'nullable',
            'margin_jual_cust' => 'nullable',
            'margin_jual_prem' => 'nullable',

        ], [
            'nama.required' => 'Nama wajib diisi.'
        ]);

        if (!$kode) {
            DB::select('call kode_barang(@nomor)');
            $nomor = DB::table('counter')->select('kode_barang')->first();
            $kode = FormatingHelper::genKodeBarang($nomor->kode_barang, 'BRG');
        }

        $barang = Barang::updateOrCreate(
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
        $barang = Barang::find($request->id);
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
