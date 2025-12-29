<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\Send\MasterHelper;
use App\Http\Controllers\Controller;
use App\Models\FailedToSend;
use App\Models\Master\Cabang;
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
                $query->where('nama', 'like', '%' . request('q') . '%')
                    ->orWhere('kode', 'like', '%' . request('q') . '%');
            });
        })->whereNull('hidden')
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

        // $dataTosend = [
        //     'kode' => $kode,
        //     'action' => 'simpan',
        //     'model' => 'dokter',
        //     'data' => $data
        // ];
        // $kirim = MasterHelper::sendMaster($dataTosend);
        // $data->load('failed');
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data barang berhasil disimpan'
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

        // $dataTosend = [
        //     'kode' => $data->kode,
        //     'action' => 'hapus',
        //     'model' => 'dokter',
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
        //         'message' => 'Data dokter di cabang ' . $cabang . ' gagal dihapus'
        //     ], 410);
        // }
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data barang berhasil dihapus'
        ]);
    }
    // public function reSend(Request $request)
    // {

    //     $data = FailedToSend::where('kode', $request->kode)->where('model', 'dokter')->get();
    //     $resp = MasterHelper::reSendMaster($data);
    //     return new JsonResponse([
    //         'req' => $request->all(),
    //         'resp' => $resp,
    //         'data' => $data,
    //     ]);
    // }
}
