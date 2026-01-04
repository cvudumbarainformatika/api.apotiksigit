<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use App\Models\Transactions\Penyesuaian;
use App\Models\Transactions\Stok;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
            'depo' => !!request('depo') && request('depo') != 'gudang' ? request('depo') : 'APS0000'
        ];

        $query = Stok::query()
            ->leftjoin('barangs', 'stoks.kode_barang', '=', 'barangs.kode')
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('barangs.kode', 'like', '%' . request('q') . '%')
                        ->orWhere('barangs.nama', 'like', '%' . request('q') . '%');
                });
            })
            ->with([
                'barang'
            ])
            ->when(request('tampil') != 'semua', function ($q) {
                $q->where('jumlah_k', '!=', 0);
            })
            ->where('kode_depo', $req['depo'])
            ->select('stoks.*')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
    public function simpanPenyesuaian(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required',
            'keterangan' => 'required',
            'id_stok' => 'required',
            'jumlah' => 'required',
            'satuan_k' => 'required',
            'kode_depo' => 'required',
        ], [
            'keterangan.required' => 'Keterangan harus diisi.',
            'id_stok.required' => 'id stok harus diisi.',
            'kode_barang.required' => 'Kode Barang harus diisi.',
            'jumlah.required' => 'Jumlah Penyesuaian harus diisi.',
            'satuan_k.required' => 'Satuan harus diisi.',
            'kode_depo.required' => 'Kode depo / Gudang harus diisi.',
        ]);
        try {
            DB::beginTransaction();
            $stok = Stok::find($validated['id_stok']);
            if (!$stok) throw new Exception('Stok tidak ditemukan, gagal membuat penyesuaian');
            if ($validated['kode_depo'] != $stok->kode_depo) throw new Exception('Stok barang yang akan diperbaiki tidak sama dengan depo / gudang tertuju');
            $sebelum = (int) $stok->jumlah_k;
            $sesudah = (int) $validated['jumlah'] + $sebelum;
            if ((int)$sesudah < 0) throw new Exception('Jumlah Setelah Penyesuaian Kurang dari 0, Perikas kembali penyesuaian anda');
            $data = Penyesuaian::create([
                'kode_barang' => $validated['kode_barang'],
                'tgl_penyesuaian' => Carbon::now()->format('Y-m-d H:i:s'),
                'keterangan' => $validated['keterangan'],
                'id_stok' => $validated['id_stok'],
                'kode_depo' => $validated['kode_depo'],
                'satuan_k' => $validated['satuan_k'],
                'jumlah_k' => $validated['jumlah'],
                'jumlah_sebelum' => $sebelum,
                'jumlah_setelah' => $sesudah,
            ]);
            $stok->update(['jumlah_k' => $sesudah]);
            DB::commit();
            return new JsonResponse([
                'message' => 'Penyesuaian sudah dibuat, dan stok sudah di sesuaikan',
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' =>  $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),

            ], 410);
        }
    }
    public function kartuStok()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
            'bulan' => request('bulan') ?? Carbon::now()->month,
            'tahun' => request('tahun') ?? Carbon::now()->year,
            'depo' => !!request('depo') && request('depo') != 'gudang' ? request('depo') : 'APS0000'
        ];
        $target = Carbon::create($req['tahun'], $req['bulan'], 1);
        $now = $target->copy()->startOfMonth();
        $last = $target->copy()->endOfMonth();
        $akhirBulanLalu = $target->copy()->subMonth()->endOfMonth();
        $lastMonth = $akhirBulanLalu->toDateString();
        $awalBulan = $now->toDateTimeString();
        $akhirBulan = $last->toDateTimeString();
        // $lastMonth = $akhirBulanLalu->toDateString();
        // return new JsonResponse([
        //     'now' => $now,
        //     'akhirBulanLalu' => $akhirBulanLalu,
        //     'lastMonth' => $lastMonth,
        //     'awalBulan' => $awalBulan,
        // ]);
        $raw = Barang::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        });
        if ($req['depo'] == 'APS0000') {
            $raw->with([
                'stokAwal' => function ($q) use ($lastMonth, $req) {
                    $q->whereDate('tgl_opname', $lastMonth)
                        ->where('kode_depo', $req['depo'])
                    ;
                },
                'stoks' => function ($q) use ($req) {
                    $q->where('kode_depo', $req['depo']);
                },
                'penyesuaian' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->whereBetween('tgl_penyesuaian', [$awalBulan, $akhirBulan])
                        ->where('kode_depo', $req['depo']);
                },
                'penerimaanRinci' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->select(
                        'penerimaan_rs.kode_barang',
                        DB::raw('sum(penerimaan_rs.jumlah_k) as jumlah_k'),
                    )
                        ->leftJoin('penerimaan_hs', 'penerimaan_hs.nopenerimaan', '=', 'penerimaan_rs.nopenerimaan')
                        ->whereBetween('penerimaan_hs.tgl_penerimaan', [$awalBulan, $akhirBulan])
                        ->whereNotNull('penerimaan_hs.flag')
                        ->groupBy('penerimaan_rs.kode_barang');
                },
                'ReturPembelianRinci' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->select(
                        'retur_pembelian_rs.kode_barang',
                        DB::raw('sum(retur_pembelian_rs.jumlahretur_k) as jumlah_k'),
                    )
                        ->leftJoin('retur_pembelian_hs', 'retur_pembelian_hs.noretur', '=', 'retur_pembelian_rs.noretur')
                        ->whereBetween('retur_pembelian_hs.tglretur', [$awalBulan, $akhirBulan])
                        ->whereNotNull('retur_pembelian_hs.flag')
                        ->groupBy('retur_pembelian_rs.kode_barang');
                },
                'mutasiMasuk' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->select(
                        'mutasi_requests.kode_barang',
                        DB::raw('sum(mutasi_requests.distribusi) as distribusi'),
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->whereBetween('mutasi_headers.tgl_terima',  [$awalBulan, $akhirBulan])
                        ->where('dari', $req['depo'])
                        ->whereNotNull('mutasi_headers.status')
                        ->groupBy('mutasi_requests.kode_barang');
                },
                'mutasiKeluar' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->select(
                        'mutasi_requests.kode_barang',
                        DB::raw('sum(mutasi_requests.distribusi) as distribusi'),
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->whereBetween('mutasi_headers.tgl_distribusi',  [$awalBulan, $akhirBulan])
                        ->where('tujuan', $req['depo'])
                        ->whereNotNull('mutasi_headers.status')
                        ->groupBy('mutasi_requests.kode_barang');
                },
            ]);
        } else {
            $raw->with([
                'stokAwal' => function ($q) use ($lastMonth, $req) {
                    $q->whereDate('tgl_opname', $lastMonth)
                        ->where('kode_depo', $req['depo'])
                    ;
                },
                'stoks' => function ($q) use ($req) {
                    $q->where('kode_depo', $req['depo']);
                },
                'penyesuaian' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->whereBetween('tgl_penyesuaian', [$awalBulan, $akhirBulan])
                        ->where('kode_depo', $req['depo']);
                },
                'penjualanRinci' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->select(
                        'penjualan_r_s.kode_barang',
                        DB::raw('sum(penjualan_r_s.jumlah_k) as jumlah_k'),
                    )
                        ->leftJoin('penjualan_h_s', 'penjualan_h_s.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
                        ->whereBetween('penjualan_h_s.tgl_penjualan', [$awalBulan, $akhirBulan])
                        ->whereNotNull('penjualan_h_s.flag')
                        ->groupBy('penjualan_r_s.kode_barang');
                },
                'returPenjualanRinci' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->select(
                        'retur_penjualan_rs.kode_barang',
                        DB::raw('sum(retur_penjualan_rs.jumlah_k) as jumlah_k'),
                    )
                        ->leftJoin('retur_penjualan_hs', 'retur_penjualan_hs.noretur', '=', 'retur_penjualan_rs.noretur')
                        ->whereBetween('retur_penjualan_hs.tgl_retur', [$awalBulan, $akhirBulan])
                        ->whereNotNull('retur_penjualan_hs.flag')
                        ->groupBy('retur_penjualan_rs.kode_barang');
                },
                'mutasiMasuk' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->select(
                        'mutasi_requests.kode_barang',
                        DB::raw('sum(mutasi_requests.distribusi) as distribusi'),
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->whereBetween('mutasi_headers.tgl_terima',  [$awalBulan, $akhirBulan])
                        ->where('dari', $req['depo'])
                        ->whereNotNull('mutasi_headers.status')
                        ->groupBy('mutasi_requests.kode_barang');
                },
                'mutasiKeluar' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->select(
                        'mutasi_requests.kode_barang',
                        DB::raw('sum(mutasi_requests.distribusi) as distribusi'),
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->whereBetween('mutasi_headers.tgl_distribusi',  [$awalBulan, $akhirBulan])
                        ->where('tujuan', $req['depo'])
                        ->whereNotNull('mutasi_headers.status')
                        ->groupBy('mutasi_requests.kode_barang');
                },
            ]);
        }
        $raw->whereNull('hidden')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);
        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
    public function kartuStokRinci()
    {
        $req = [
            'bulan' => request('bulan') ?? Carbon::now()->month,
            'tahun' => request('tahun') ?? Carbon::now()->year,
            'depo' => !!request('depo') && request('depo') != 'gudang' ? request('depo') : 'APS0000'
        ];
        $target = Carbon::create($req['tahun'], $req['bulan'], 1);
        $now = $target->copy()->startOfMonth();
        $last = $target->copy()->endOfMonth();
        $akhirBulanLalu = $target->copy()->subMonth()->endOfMonth();
        $lastMonth = $akhirBulanLalu->toDateString();
        $awalBulan = $now->toDateTimeString();
        $akhirBulan = $last->toDateTimeString();
        // $akhirBulanLalu = Carbon::parse($req['from'])->subMonth()->endOfMonth();
        // $lastMonth = $akhirBulanLalu->toDateTimeString();
        $lastMonth = $akhirBulanLalu->toDateString();
        $raw = Barang::query()->where('id', request('id'));
        if ($req['depo'] == 'APS0000') {
            $raw->with([
                'stokAwal' => function ($q) use ($lastMonth, $req) {
                    $q->where('kode_depo', $req['depo'])
                        ->whereDate('tgl_opname', $lastMonth);
                },
                'stok' => function ($q) use ($req) {
                    $q->where('kode_depo', $req['depo']);
                },
                'penyesuaian' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->whereBetween('tgl_penyesuaian', [$awalBulan, $akhirBulan])
                        ->where('kode_depo', $req['depo']);
                },
                'penerimaanRinci' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->select(
                        'penerimaan_rs.kode_barang',
                        'penerimaan_rs.jumlah_k',
                        'penerimaan_hs.nopenerimaan',
                        'penerimaan_hs.tgl_penerimaan',
                    )
                        ->leftJoin('penerimaan_hs', 'penerimaan_hs.nopenerimaan', '=', 'penerimaan_rs.nopenerimaan')
                        ->whereBetween('penerimaan_hs.tgl_penerimaan', [$awalBulan, $akhirBulan])
                        ->whereNotNull('penerimaan_hs.flag');
                },
                'ReturPembelianRinci' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->select(
                        'retur_pembelian_rs.kode_barang',
                        'retur_pembelian_rs.jumlahretur_k as jumlah_k',
                        'retur_pembelian_hs.noretur',
                        'retur_pembelian_hs.tglretur',
                    )
                        ->leftJoin('retur_pembelian_hs', 'retur_pembelian_hs.noretur', '=', 'retur_pembelian_rs.noretur')
                        ->whereBetween('retur_pembelian_hs.tglretur', [$awalBulan, $akhirBulan])
                        ->whereNotNull('retur_pembelian_hs.flag');
                },
                'mutasiMasuk' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->select(
                        'mutasi_requests.kode_barang',
                        'mutasi_requests.distribusi',
                        'mutasi_headers.tgl_terima',
                        // 'mutasi_headers.tujuan',
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->whereBetween('mutasi_headers.tgl_terima',  [$awalBulan, $akhirBulan])
                        ->where('dari', $req['depo'])
                        ->whereNotNull('mutasi_headers.status');
                },
                'mutasiKeluar' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->select(
                        'mutasi_requests.kode_barang',
                        'mutasi_requests.distribusi',
                        'mutasi_headers.tgl_distribusi',
                        // 'mutasi_headers.tujuan',
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->whereBetween('mutasi_headers.tgl_distribusi',  [$awalBulan, $akhirBulan])
                        ->where('tujuan', $req['depo'])
                        ->whereNotNull('mutasi_headers.status');
                },
            ]);
        } else {
            $raw->with([
                'stokAwal' => function ($q) use ($lastMonth, $req) {
                    $q->where('kode_depo', $req['depo'])
                        ->whereDate('tgl_opname', $lastMonth);
                },
                'stok' => function ($q) use ($req) {
                    $q->where('kode_depo', $req['depo']);
                },
                'penyesuaian' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->whereBetween('tgl_penyesuaian', [$awalBulan, $akhirBulan])
                        ->where('kode_depo', $req['depo']);
                },
                'penjualanRinci' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->select(
                        'penjualan_r_s.kode_barang',
                        'penjualan_r_s.jumlah_k',
                        'penjualan_h_s.tgl_penjualan',
                        'penjualan_h_s.nopenjualan',
                    )
                        ->leftJoin('penjualan_h_s', 'penjualan_h_s.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
                        ->whereBetween('penjualan_h_s.tgl_penjualan', [$awalBulan, $akhirBulan])
                        ->whereNotNull('penjualan_h_s.flag');
                },
                'returPenjualanRinci' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->select(
                        'retur_penjualan_rs.kode_barang',
                        'retur_penjualan_rs.jumlah_k',
                        'retur_penjualan_hs.tgl_retur',
                        'retur_penjualan_hs.noretur',
                    )
                        ->leftJoin('retur_penjualan_hs', 'retur_penjualan_hs.noretur', '=', 'retur_penjualan_rs.noretur')
                        ->whereBetween('retur_penjualan_hs.tgl_retur', [$awalBulan, $akhirBulan])
                        ->whereNotNull('retur_penjualan_hs.flag');
                },
                'mutasiMasuk' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->select(
                        'mutasi_requests.kode_mutasi',
                        'mutasi_requests.kode_barang',
                        'mutasi_requests.distribusi',
                        'mutasi_headers.tgl_terima',
                        // 'mutasi_headers.tujuan',
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->whereBetween('mutasi_headers.tgl_terima',  [$awalBulan, $akhirBulan])
                        ->where('dari', $req['depo'])
                        ->whereNotNull('mutasi_headers.status');
                },
                'mutasiKeluar' => function ($q) use ($awalBulan, $akhirBulan, $req) {
                    $q->select(
                        'mutasi_requests.kode_mutasi',
                        'mutasi_requests.kode_barang',
                        'mutasi_requests.distribusi',
                        'mutasi_headers.tgl_distribusi',
                        // 'mutasi_headers.tujuan',
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->whereBetween('mutasi_headers.tgl_distribusi',  [$awalBulan, $akhirBulan])
                        ->where('tujuan', $req['depo'])
                        ->whereNotNull('mutasi_headers.status');
                },
            ]);
        }
        $data = $raw->first();
        return new JsonResponse([
            'data' => $data,
            'req' => request()->all(),
        ]);
    }
}
