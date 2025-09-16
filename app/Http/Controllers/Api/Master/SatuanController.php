<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Satuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SatuanController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];

        $raw = Satuan::query();

        $raw->when(request('q'), function ($q) {
            $q->where(function ($query) {
                $query->where('nama', 'like', '%' . request('q') . '%')
                    ->orWhere('kode', 'like', '%' . request('q') . '%');
            });
        })
            ->whereNull('hidden')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);

        $totalCount = (clone $raw)->count();

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
        ], [
            'nama.required' => 'Nama wajib diisi.'
        ]);

        if (!$kode) {
            DB::select('call kode_satuan(@nomor)');
            $nomor = DB::table('counter')->select('kode_satuan')->first();
            $validated['kode'] = FormatingHelper::genKodeDinLength($nomor->kode_satuan, 5, 'STN');
        }

        $data = Satuan::updateOrCreate(
            [
                'kode' => $validated['kode']
            ],
            $validated
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Satuan berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $data = Satuan::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Satuan tidak ditemukan'
            ], 410);
        }
        $data->update(['hidden' => '1']);
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Satuan berhasil dihapus'
        ]);
    }
}
