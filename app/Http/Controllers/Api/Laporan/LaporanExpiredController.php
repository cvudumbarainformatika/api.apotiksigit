<?php

namespace App\Http\Controllers\Api\Laporan;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Stok;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaporanExpiredController extends Controller
{
    //
    public function index()
    {
        $req = [
            // 'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => request('from') ?? null,
            'to' => request('to') ?? null,
        ];
        $raw = Stok::query();
        $raw->leftJoin('barangs', 'barangs.kode', '=', 'stoks.kode_barang')
            ->when(request('q'), function ($q) {
                $q->where('nama', 'like', '%' . request('q') . '%')
                    ->orWhere('kode', 'like', '%' . request('q') . '%');
            })
            ->where('jumlah_k', '>', 0)
            ->when($req['from'] && $req['to'], function ($q) use ($req) {
                $q->whereDate('tgl_exprd', '>=', $req['from'])
                    ->whereDate('tgl_exprd', '<', $req['to']);
            })
            ->select(
                'barangs.kode',
                'barangs.nama',
                'barangs.satuan_k',
                'barangs.satuan_b',
                'stoks.jumlah_k',
                'stoks.isi',
                'stoks.tgl_exprd',
            )
            ->orderBy('tgl_exprd', $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
}
