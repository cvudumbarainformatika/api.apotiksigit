<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Apoteker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApotekerController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];

        $raw = Apoteker::query();

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
        $kode = $request->kode;
        $validated = $request->validate([
            'nama' => 'required',
            'kode' => 'nullable',
            'sipa' => 'required',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'sipa.required' => 'SIPA wajib diisi.'
        ]);

        if (!$kode) {
            $lastId = Apoteker::max('id');
            $validated['kode'] = FormatingHelper::genKodeDinLength(($lastId ?? 0)  + 1, 4, 'APT');
        }
        try {
            DB::beginTransaction();
            $data = Apoteker::updateOrCreate(
                [
                    'kode' => $validated['kode']
                ],
                $validated
            );
            DB::commit();
            return new JsonResponse([
                'data' => $data,
                'message' => 'Data Apoteker berhasil disimpan'
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ]);
        }
    }

    public function hapus(Request $request)
    {
        $data = Apoteker::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Apoteker tidak ditemukan'
            ], 410);
        }
        $data->update(['hidden' => '1']);
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Apoteker berhasil dihapus'
        ]);
    }
}
