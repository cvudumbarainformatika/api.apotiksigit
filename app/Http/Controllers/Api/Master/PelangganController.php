<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Pelanggan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{

    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];

        $raw = Pelanggan::query();

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
            'alamat' => 'nullable',
        ], [
            'nama.required' => 'Nama wajib diisi.'
        ]);

        if (!$kode) {
            DB::select('call kode_pelanggan(@nomor)');
            $nomor = DB::table('counter')->select('kode_pelanggan')->first();
            $validated['kode'] = FormatingHelper::genKodeDinLength($nomor->kode_pelanggan, 5, 'PLG');
        }

        $data = Pelanggan::updateOrCreate(
            [
                'kode' => $validated['kode']
            ],
            $validated
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Pelanggan berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $data = Pelanggan::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Pelanggan tidak ditemukan'
            ], 410);
        }
        $data->update(['hidden' => '1']);
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Pelanggan berhasil dihapus'
        ]);
    }
}
