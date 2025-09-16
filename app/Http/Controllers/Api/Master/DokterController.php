<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Dokter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DokterController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];

        $raw = Dokter::query();

        $raw->when(request('q'), function ($q) {
            $q->where(function ($query) {
                $query->where('nama_dokter', 'like', '%' . request('q') . '%')
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
        $kode = $request->kode;
        $validated = $request->validate([
            'nama_dokter' => 'required',
            'kode' => 'nullable',
            'alamat' => 'required',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.'
        ]);

        if (!$kode) {
            DB::select('call kode_dokter(@nomor)');
            $nomor = DB::table('counter')->select('kode_dokter')->first();
            $validated['kode'] = FormatingHelper::genKodeDinLength($nomor->kode_dokter, 4, 'DK');
        }

        $data = Dokter::updateOrCreate(
            [
                'kode' => $validated['kode']
            ],
            $validated
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Dokter berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $data = Dokter::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Dokter tidak ditemukan'
            ], 410);
        }
        $data->update(['hidden' => '1']);
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Dokter berhasil dihapus'
        ]);
    }
}
