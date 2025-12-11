<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\Send\MasterHelper;
use App\Http\Controllers\Controller;
use App\Models\FailedToSend;
use App\Models\Master\Beban;
use App\Models\Master\Cabang;
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
                $query->where('nama', 'like', '%' . request('q') . '%');
            });
        })->where('flag', '')
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
        $validated = $request->validate([
            'nama' => 'required',
        ], [
            'nama.required' => 'Nama wajib diisi.',
        ]);

        $data = Beban::updateOrCreate(
            [
                'nama' => $validated['nama'],
                'flag' => ''
            ],
            $validated
        );
        $kode = FormatingHelper::genKodeBarang($data->id, 'BBN');
        $data->update(['kode' => $kode]);

        // $dataTosend = [
        //     'kode' => $kode,
        //     'action' => 'simpan',
        //     'model' => 'beban',
        //     'data' => $data
        // ];
        // $kirim = MasterHelper::sendMaster($dataTosend);
        // $data->load('failed');
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data beban berhasil disimpan'
        ], 410);
    }

    public function hapus(Request $request)
    {
        $data = Beban::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Beban tidak ditemukan'
            ], 410);
        }
        // $data->update(['flag' => '1']);
        // $dataTosend = [
        //     'kode' => $data->kode,
        //     'action' => 'hapus',
        //     'model' => 'beban',
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
        //         'kirim' => $kirim,
        //         'message' => 'Data barang di cabang ' . $cabang . ' gagal dihapus'
        //     ], 410);
        // }
        return new JsonResponse([
            'data' => $data,
            // 'kirim' => $kirim,
            'message' => 'Data barang berhasil dihapus'
        ]);
    }
    // public function reSend(Request $request)
    // {

    //     $data = FailedToSend::where('kode', $request->kode)->where('model', 'beban')->get();
    //     $resp = MasterHelper::reSendMaster($data);
    //     return new JsonResponse([
    //         'req' => $request->all(),
    //         'resp' => $resp,
    //         'data' => $data,
    //     ]);
    // }
}
