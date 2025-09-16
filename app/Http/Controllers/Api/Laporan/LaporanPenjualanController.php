<?php

namespace App\Http\Controllers\Api\Laporan;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use App\Models\Transactions\PenjualanH;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanController extends Controller
{
    //
    public function barang()
    {
        $req = [
            'order_by' => request('order_by') ?? 'nama',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => request('from') ?? null,
            'to' => request('to') ?? null,
        ];
        $raw = Barang::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->with([
                'penjualanRinci' => function ($q) use ($req) {
                    $q->select(
                        'penjualan_r_s.kode_barang',
                        'penjualan_r_s.nopenjualan',
                        'penjualan_r_s.nobatch',
                        'penjualan_r_s.tgl_exprd',
                        'penjualan_r_s.jumlah_k',
                        'penjualan_r_s.satuan_k',
                        'penjualan_r_s.harga_jual',
                        'penjualan_r_s.harga_beli',
                        'penjualan_r_s.subtotal',
                        'penjualan_h_s.tgl_penjualan',
                    )
                        ->leftJoin('penjualan_h_s', 'penjualan_h_s.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
                        ->whereBetween('penjualan_h_s.tgl_penjualan', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59']);
                },
                'returPenjualanRinci' => function ($q) use ($req) {
                    $q->select(
                        'retur_penjualan_rs.kode_barang',
                        'retur_penjualan_rs.nopenjualan',
                        'retur_penjualan_rs.noretur',
                        'retur_penjualan_rs.nobatch',
                        'retur_penjualan_rs.jumlah_k',
                        'retur_penjualan_rs.satuan_k',
                        'retur_penjualan_rs.harga',
                        DB::raw('retur_penjualan_rs.jumlah_k * retur_penjualan_rs.harga as subtotal'),
                        'retur_penjualan_hs.tgl_retur',
                    )
                        ->leftJoin('retur_penjualan_hs', 'retur_penjualan_hs.noretur', '=', 'retur_penjualan_rs.noretur')
                        ->whereBetween('retur_penjualan_hs.tgl_retur', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59']);
                },
            ])
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
    public function transakction()
    {
        $req = [
            'order_by' => request('order_by') ?? 'tgl_penjualan',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => request('from') ?? null,
            'to' => request('to') ?? null,
        ];

        $allowed = ['created_at', 'tgl_penjualan', 'nopenjualan']; // whitelist biar aman
        $orderBy = in_array($req['order_by'], $allowed) ? $req['order_by'] : 'created_at';
        $raw = PenjualanH::query();
        $raw->when($req['from'] && $req['to'], function ($q) use ($req) {
            $q->whereBetween('penjualan_h_s.tgl_penjualan', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59']);
        })
            ->with([
                'rinci' => function ($q) {
                    $q->with('master:kode,nama')
                        ->select(
                            'penjualan_r_s.kode_barang',
                            'penjualan_r_s.nopenjualan',
                            'penjualan_r_s.nobatch',
                            'penjualan_r_s.tgl_exprd',
                            'penjualan_r_s.jumlah_k',
                            'penjualan_r_s.satuan_k',
                            'penjualan_r_s.harga_jual',
                            'penjualan_r_s.harga_beli',
                            'penjualan_r_s.subtotal',
                            DB::raw('COALESCE(retur_penjualan_rs.noretur, "") as noretur'),
                            DB::raw('COALESCE(retur_penjualan_rs.jumlah_k, 0) as retur'),
                            DB::raw('COALESCE(retur_penjualan_rs.jumlah_k, 0) * COALESCE(retur_penjualan_rs.harga, 0) as subtotal_retur')
                        )
                        ->leftJoin('retur_penjualan_rs', function ($q) {
                            $q->on('retur_penjualan_rs.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
                                ->on('retur_penjualan_rs.kode_barang', '=', 'penjualan_r_s.kode_barang')
                                ->on('retur_penjualan_rs.id_stok', '=', 'penjualan_r_s.id_stok');
                        })
                    ;
                },
                'pelanggan',
                'dokter',
            ])
            // ->orderBy($req['order_by'], $req['sort']);
            ->orderBy("penjualan_h_s.$orderBy", $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);
        // Hitung total per penjualan (dari relasi rinci)
        // $data->getCollection()->transform(function ($item) {
        //     $item->total_subtotal = $item->rinci->sum('subtotal');
        //     $item->total_subtotal_retur = $item->rinci->sum('subtotal_retur');
        //     return $item;
        // });

        $grandTotals = (clone $raw)
            ->selectRaw('
            SUM(penjualan_r_s.subtotal) as total_subtotal,
            SUM(COALESCE(retur_penjualan_rs.jumlah_k, 0) * COALESCE(retur_penjualan_rs.harga, 0)) as total_subtotal_retur
        ')
            ->leftJoin('penjualan_r_s', 'penjualan_r_s.nopenjualan', '=', 'penjualan_h_s.nopenjualan')
            ->leftJoin('retur_penjualan_rs', function ($q) {
                $q->on('retur_penjualan_rs.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
                    ->on('retur_penjualan_rs.kode_barang', '=', 'penjualan_r_s.kode_barang')
                    ->on('retur_penjualan_rs.id_stok', '=', 'penjualan_r_s.id_stok');
            })
            ->first();


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        $resp['grand_total'] = [
            'total_subtotal' => (float) $grandTotals->total_subtotal,
            'total_subtotal_retur' => (float) $grandTotals->total_subtotal_retur,
            'total_penjualan' => (float) $grandTotals->total_subtotal - (float) $grandTotals->total_subtotal_retur
        ];
        return new JsonResponse($resp);
    }
}
