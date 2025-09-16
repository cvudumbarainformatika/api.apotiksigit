<?php

namespace App\Http\Controllers\Api\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Transactions\Penerimaan_h;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanHutangController extends Controller
{
    public function index()
    {
        $data = Penerimaan_h::query()
        ->leftjoin('retur_pembelian_hs', 'penerimaan_hs.nopenerimaan', '=', 'retur_pembelian_hs.nopenerimaan')
        ->leftjoin('retur_pembelian_rs', 'retur_pembelian_hs.noretur', '=', 'retur_pembelian_rs.noretur')
        ->leftJoin('penerimaan_rs', 'penerimaan_hs.nopenerimaan', '=', 'penerimaan_rs.nopenerimaan') // join ke rinci
        ->join('suppliers', 'penerimaan_hs.kode_suplier', '=', 'suppliers.kode')
        ->where('penerimaan_hs.hutang','Hutang')
        ->whereBetween('penerimaan_hs.tgl_penerimaan', [request('from'), request('to')])
        ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('penerimaan_hs.nopenerimaan', 'like', '%' . request('q') . '%')
                            ->orWhere('penerimaan_hs.noorder', 'like', '%' . request('q') . '%')
                            ->orWhere('suppliers.nama', 'like', '%' . request('q') . '%');
                });
            })
         ->select(
            'penerimaan_hs.*',
            'suppliers.nama as suplier',
            DB::raw('SUM(penerimaan_rs.subtotal) as total_rinci'),
            DB::raw('SUM(retur_pembelian_rs.subtotal) as total_retur')
        )
        ->groupBy( 'penerimaan_hs.id',
            'penerimaan_hs.nopenerimaan',
            'penerimaan_hs.noorder',
            'penerimaan_hs.tgl_penerimaan',
            'penerimaan_hs.kode_suplier',
            'penerimaan_hs.hutang',
            'suppliers.nama') // penting untuk agregat
        ->orderBy('penerimaan_hs.tgl_penerimaan', 'asc')
        ->get();
        return new JsonResponse([
            'data' => $data
        ]);
    }
}
