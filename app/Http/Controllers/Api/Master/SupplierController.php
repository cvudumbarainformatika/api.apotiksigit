<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];

        $raw = Supplier::query();
        $raw->when(request('q'), function ($q) {
            $q->where(function ($query) {
                $query->where('nama', 'like', '%' . request('q') . '%')
                    ->orWhere('kode', 'like', '%' . request('q') . '%');
            });
        })->whereNull('hidden')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function store(Request $request)
    {
        // return new JsonResponse($request->all());
        $kode = $request->kode;
        $validated = $request->validate([
            'nama' => 'required',
            'kode' => 'nullable',
            'tlp' => 'nullable',
            'bank' => 'nullable',
            'rekening' => 'nullable',
            'alamat' => 'nullable',
        ], [
            'nama.required' => 'Nama wajib diisi.'
        ]);

        if (!$kode) {
            DB::select('call kode_supplier(@nomor)');
            $nomor = DB::table('counter')->select('kode_supplier')->first();
            $validated['kode'] = FormatingHelper::genKodeDinLength($nomor->kode_supplier, 5, 'PBF');
        }

        $data = Supplier::updateOrCreate(
            [
                'kode' => $validated['kode']
            ],
            $validated
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Supplier berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $data = Supplier::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Supplier tidak ditemukan'
            ], 410);
        }
        $data->update(['hidden' => '1']);
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Supplier berhasil dihapus'
        ]);
    }
}
