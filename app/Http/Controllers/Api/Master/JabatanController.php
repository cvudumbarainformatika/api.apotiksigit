<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Jabatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JabatanController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];

        $raw = Jabatan::query();

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
        ], [
            'nama.required' => 'Nama wajib diisi.'
        ]);

        if (!$kode) {
            DB::select('call kode_jabatan(@nomor)');
            $nomor = DB::table('counter')->select('kode_jabatan')->first();
            $validated['kode'] = FormatingHelper::genKodeDinLength($nomor->kode_jabatan, 4, 'JBT');
        }

        $data = Jabatan::updateOrCreate(
            [
                'kode' => $validated['kode']
            ],
            $validated
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Jabatan berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $jabatan = Jabatan::find($request->id);
        if (!$jabatan) {
            return new JsonResponse([
                'message' => 'Data Jabatan tidak ditemukan'
            ], 410);
        }
        $jabatan->update(['hidden' => '1']);
        return new JsonResponse([
            'data' => $jabatan,
            'message' => 'Data Jabatan berhasil dihapus'
        ]);
    }
}
