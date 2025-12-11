<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\Send\MasterHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Cabang;
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
        $data = $raw
            // ->with('failed')
            ->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function store(Request $request)
    {
        // $cek = MasterHelper::isGundangHere();
        // if (!$cek) {
        //     return new JsonResponse([
        //         'cabang' => $cek,
        //         'message' => 'Perubahan data Master hanya bisa dilakukan di cabang gundang'
        //     ], 410);
        // }
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

        $data = KategoriExpired::updateOrCreate(
            [
                'kode' =>  $kode
            ],
            $validated
        );

        // $dataTosend = [
        //     'kode' => $kode,
        //     'action' => 'simpan',
        //     'model' => 'barang',
        //     'data' => $data
        // ];
        // $kirim = MasterHelper::sendMaster($dataTosend);
        // $data->load('failed');
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data barang berhasil disimpan'
        ], 410);
    }

    public function hapus(Request $request)
    {
        $data = KategoriExpired::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data kategori tidak ditemukan'
            ], 410);
        }

        // $dataTosend = [
        //     'kode' => $data->kode,
        //     'action' => 'hapus',
        //     'model' => 'barang',
        //     'data' => $data
        // ];
        // $kirim = MasterHelper::sendMaster($dataTosend);
        // $failed = $kirim['fails'];

        // if (empty($failed)) {
        $data->update(['hidden' => '1']);
        // } else {
        //     $urls = array_column($failed, 'url');
        //     $cabang = Cabang::whereIn('url', $urls)->pluck('namacabang')->implode(', ');
        //     return new JsonResponse([
        //         'data' => $data,
        //         'message' => 'Data barang di cabang ' . $cabang . ' gagal dihapus'
        //     ], 410);
        // }
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data barang berhasil dihapus'
        ]);
    }
}
