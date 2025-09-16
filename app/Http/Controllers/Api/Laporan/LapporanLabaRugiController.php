<?php

namespace App\Http\Controllers\Api\Laporan;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Beban;
use App\Models\Transactions\Beban_h;
use App\Models\Transactions\PenjualanH;
use App\Models\Transactions\ReturPenjualan_h;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LapporanLabaRugiController extends Controller
{
    //
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => request('from') ?? null,
            'to' => request('to') ?? null,
        ];
        $data = [];
        $penjualan = PenjualanH::when($req['from'] && $req['to'], function ($q) use ($req) {
            $q->whereBetween('tgl_penjualan', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59']);
        })
            ->withSum('rinci', 'subtotal') // ini akan menambah kolom rinci_sum_subtotal
            ->withSum('rinci', DB::raw('jumlah_k * harga_beli')) // sum hpp
            ->withSum('rinci', DB::raw('jumlah_k * harga_jual')) // sum penjualan
            ->whereNotNull('flag')
            ->get();
        $returPenjualan = ReturPenjualan_h::when($req['from'] && $req['to'], function ($q) use ($req) {
            $q->whereBetween('tgl_retur', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59']);
        })
            ->withSum('returPenjualan_r', DB::raw('jumlah_k * harga'))
            ->withSum('returPenjualan_r', DB::raw('jumlah_k * harga_beli'))
            ->whereNotNull('flag')
            ->get();
        $beban = Beban::withSum([
            'rincian as subtotal' => function ($q) use ($req) {
                $q->leftJoin('beban_hs', 'beban_hs.notransaksi', '=', 'beban_rs.notransaksi')
                    ->whereBetween('beban_hs.created_at', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59'])
                    ->whereNotNull('beban_hs.flag');
            }
        ], 'subtotal')
            ->get();
        // $beban = Beban_h::when($req['from'] && $req['to'], function ($q) use ($req) {
        //     $q->whereBetween('created_at', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59']);
        // })->withSum('rincian', 'subtotal')
        //     ->with([
        //         'rincian' => function ($q) {
        //             $q
        //                 ->select(
        //                     'kode_beban',
        //                     'notransaksi',
        //                     'subtotal',
        //                 )
        //                 ->with('mbeban');
        //         },
        //     ])
        //     ->whereNotNull('flag')
        //     ->get();
        $totalPenjualan = (int)$penjualan->sum('rinci_sum_subtotal');
        $hppPenjualan = (int)$penjualan->sum('rinci_sum_jumlah_k_harga_beli');
        $totalReturPenjualan = (int)$returPenjualan->sum('retur_penjualan_r_sum_jumlah_k_harga');
        $hppReturPenjualan = (int)$returPenjualan->sum('retur_penjualan_r_sum_jumlah_k_harga_beli');
        $totalbeban = (int)$beban->sum('subtotal');
        $penjualanBersih = $totalPenjualan - $totalReturPenjualan;
        $hppPenjualanBersih = $hppPenjualan - $hppReturPenjualan;
        $labaKotor = $penjualanBersih - $hppPenjualanBersih;
        $labaBersih = $labaKotor - $totalbeban;


        $data['hppPenjualanBersih'] = $hppPenjualanBersih;
        $data['totalPenjualan'] = $totalPenjualan;
        $data['hppPenjualan'] = $hppPenjualan;
        $data['hppReturPenjualan'] = $hppReturPenjualan;
        $data['totalReturPenjualan'] = $totalReturPenjualan;
        $data['totalbeban'] = $totalbeban;
        $data['penjualanBersih'] = $penjualanBersih;
        $data['labaKotor'] = $labaKotor;
        $data['labaBersih'] = $labaBersih;

        $data['rincianPenjualan'] = $penjualan;
        $data['rincianReturPenjualan'] = $returPenjualan;
        $data['rincianbeban'] = $beban;

        return new JsonResponse(['data' => $data]);
    }
}
