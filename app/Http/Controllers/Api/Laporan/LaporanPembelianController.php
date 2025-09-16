<?php

namespace App\Http\Controllers\Api\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Transactions\Penerimaan_h;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaporanPembelianController extends Controller
{
    public function index()
    {
        $data =Penerimaan_h::query()
        ->join('suppliers', 'penerimaan_hs.kode_suplier', '=', 'suppliers.kode')
        ->whereBetween('penerimaan_hs.tgl_penerimaan', [request('from'), request('to')])
        ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('penerimaan_hs.nopenerimaan', 'like', '%' . request('q') . '%')
                            ->orWhere('penerimaan_hs.noorder', 'like', '%' . request('q') . '%')
                            ->orWhere('suppliers.nama', 'like', '%' . request('q') . '%');
                });
            })
        ->with(
            [
                'rincian' => function ($q) {
                    $q->with(['barang']);
                },
                'retur' => function ($q) {
                    $q->with(
                        [
                            'rincian' => function ($q) {
                                $q->with(['barang']);
                            },
                        ]);
                },
            ]
        )
        ->select('penerimaan_hs.*','suppliers.nama as suplier')
        ->orderBy('penerimaan_hs.tgl_penerimaan', 'asc')
        ->get();
        return new JsonResponse([
            'data' => $data
        ]);
    }
}
