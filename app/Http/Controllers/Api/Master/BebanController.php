<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Beban;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BebanController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];

        $raw = Beban::query();

        $raw->when(request('q'), function ($q) {
            $q->where(function ($query) {
                $query->where('nama_beban', 'like', '%' . request('q') . '%');
            });
        })->where('flag', '')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_beban' => 'required',
        ], [
            'nama_beban.required' => 'Nama wajib diisi.',
        ]);

        $data = Beban::updateOrCreate(
            [
                'nama_beban' => $validated['nama_beban'],
                'flag' => ''
            ],
            $validated
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Beban berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $data = Beban::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Beban tidak ditemukan'
            ], 410);
        }
        $data->update(['flag' => '1']);
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Beban berhasil dihapus'
        ]);
    }

}
