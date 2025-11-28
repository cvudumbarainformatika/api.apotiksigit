<?php

namespace App\Http\Controllers\Api\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LaporanFastMovingController extends Controller
{
    // public function laporanfastMoving()
    // {
    //     $req = [
    //         'order_by' => request('order_by') ?? 'nama',
    //         'sort' => request('sort') ?? 'desc',
    //         'from' => request('from') ?? null,
    //         'to' => request('to') ?? null,
    //     ];
    //     $data = Barang::query();

    //     $data->when(request('q'), function ($q) {
    //         $q->where('nama', 'like', '%' . request('q') . '%')
    //             ->orWhere('kode', 'like', '%' . request('q') . '%');
    //     })
    //     ->with([
    //         'penjualanRinci' => function ($q) use ($req) {
    //             $q->select(
    //                 'penjualan_r_s.kode_barang',
    //                 DB::raw('SUM(penjualan_r_s.jumlah_k) as jumlah_penjualan'),
    //                 'penjualan_r_s.satuan_k',
    //                 DB::raw('SUM(retur_penjualan_rs.jumlah_k) as jumlah_retur')
    //             )
    //             ->leftJoin('penjualan_h_s', 'penjualan_h_s.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
    //             ->leftJoin('retur_penjualan_rs', 'retur_penjualan_rs.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
    //             ->whereBetween('penjualan_h_s.tgl_penjualan', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59'])
    //             ->groupBy('penjualan_r_s.kode_barang', 'penjualan_r_s.satuan_k');
    //         }
    //     ])
    //     ->orderBy($req['order_by'], $req['sort'])
    //     ->get();

    //     return new JsonResponse([
    //         'data' => $data
    //     ]);
    // }

    public function laporanfastMoving()
    {
        $req = [
            'from' => request('from') ?? now()->startOfMonth()->toDateString(),
            'to' => request('to') ?? now()->endOfMonth()->toDateString(),
        ];

        $data = DB::table('penjualan_r_s')
            ->select(
                'barangs.kode',
                'barangs.nama',
                DB::raw('GROUP_CONCAT(DISTINCT penjualan_r_s.satuan_k ORDER BY penjualan_r_s.satuan_k SEPARATOR ", ") AS satuan_list'),
                DB::raw('SUM(penjualan_r_s.jumlah_k) AS jumlah_penjualan'),
                DB::raw('IFNULL(SUM(retur_penjualan_rs.jumlah_k), 0) AS jumlah_retur'),
                DB::raw('(SUM(penjualan_r_s.jumlah_k) - IFNULL(SUM(retur_penjualan_rs.jumlah_k), 0)) AS total_penjualan'),
                DB::raw('SUM(penjualan_r_s.subtotal) AS total_harga_jual'),
                DB::raw('IFNULL((SUM(retur_penjualan_rs.jumlah_k * retur_penjualan_rs.harga)-SUM(retur_penjualan_rs.diskon)), 0) AS total_harga_retur'),
                DB::raw('round(SUM(penjualan_r_s.subtotal) - IFNULL((SUM(retur_penjualan_rs.jumlah_k * retur_penjualan_rs.harga)-SUM(retur_penjualan_rs.diskon)), 0)) AS total_harga_bersih',2)
            )
            ->leftJoin('penjualan_h_s', 'penjualan_h_s.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
            ->leftJoin('retur_penjualan_rs', 'retur_penjualan_rs.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
            ->leftJoin('barangs', 'barangs.kode', '=', 'penjualan_r_s.kode_barang')
            ->whereBetween('penjualan_h_s.tgl_penjualan', [
                $req['from'] . ' 00:00:00',
                $req['to'] . ' 23:59:59'
            ])
            ->when(request('q'), function ($q) {
                $q->where('barangs.nama', 'like', '%' . request('q') . '%')
                ->orWhere('barangs.kode', 'like', '%' . request('q') . '%');
            })
            ->groupBy('penjualan_r_s.kode_barang', 'barangs.kode', 'barangs.nama')
            ->orderByRaw('(SUM(penjualan_r_s.jumlah_k) - IFNULL(SUM(retur_penjualan_rs.jumlah_k), 0)) DESC')
            ->get();

        return new JsonResponse([
            'data' => $data
        ]);
    }



}
