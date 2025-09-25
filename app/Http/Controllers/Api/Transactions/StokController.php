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
        ];

        $query = Stok::query()
            ->leftjoin('barangs', 'stoks.kode_barang', '=', 'barangs.kode')
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('stoks.nopenerimaan', 'like', '%' . request('q') . '%')
                        ->orWhere('stoks.noorder', 'like', '%' . request('q') . '%')
                        ->orWhere('barangs.nama', 'like', '%' . request('q') . '%');
                });
            })
            ->with([
                'barang'
            ])
            ->when(request('tampil') != 'semua', function ($q) {
                $q->where('jumlah_k', '>', 0);
            })
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
        ], [
            'keterangan.required' => 'Keterangan harus diisi.',
            'id_stok.required' => 'id stok harus diisi.',
            'kode_barang.required' => 'Kode Barang harus diisi.',
            'jumlah.required' => 'Jumlah Penyesuaian harus diisi.',
            'satuan_k.required' => 'Satuan harus diisi.',
        ]);
        try {
            DB::beginTransaction();
            $stok = Stok::find($validated['id_stok']);
            if (!$stok) throw new Exception('Stok tidak ditemukan, gagal membuat penyesuaian');
            $sebelum = (int) $stok->jumlah_k;
            $sesudah = (int) $validated['jumlah'] + $sebelum;
            if ((int)$sesudah < 0) throw new Exception('Jumlah Setelah Penyesuaian Kurang dari 0, Perikas kembali penyesuaian anda');
            $data = Penyesuaian::create([
                'kode_barang' => $validated['kode_barang'],
                'tgl_penyesuaian' => Carbon::now()->format('Y-m-d H:i:s'),
                'keterangan' => $validated['keterangan'],
                'id_stok' => $validated['id_stok'],
                'id_penerimaan_rinci' => $stok->id_penerimaan_rinci,
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
        })
            ->with([
                'stokAwal' => function ($q) use ($lastMonth) {
                    $q->select(
                        'kode_barang',
                        DB::raw('sum(jumlah_k) as jumlah_k'),
                    )
                        ->where('jumlah_k', '>', 0)
                        ->whereDate('tgl_opname', $lastMonth)
                        ->groupBy('kode_barang');
                },
                'stok' => function ($q) {
                    $q->select(
                        'kode_barang',
                        DB::raw('sum(jumlah_k) as jumlah_k'),
                    )
                        ->where('jumlah_k', '>', 0)
                        ->groupBy('kode_barang');
                },
                'penyesuaian' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->select(
                        'kode_barang',
                        DB::raw('sum(jumlah_k) as jumlah_k'),
                    )
                        ->whereBetween('tgl_penyesuaian', [$awalBulan, $akhirBulan])
                        ->groupBy('kode_barang');
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
            ])
            ->whereNull('hidden')
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
        ];
        $target = Carbon::create($req['tahun'], $req['bulan'], 1);
        $now = $target->copy()->startOfMonth();
        $last = $target->copy()->endOfMonth();
        $akhirBulanLalu = $target->copy()->subMonth()->endOfMonth();
        $lastMonth = $akhirBulanLalu->toDateTimeString();
        $awalBulan = $now->toDateTimeString();
        $akhirBulan = $last->toDateTimeString();
        // $akhirBulanLalu = Carbon::parse($req['from'])->subMonth()->endOfMonth();
        // $lastMonth = $akhirBulanLalu->toDateTimeString();
        $lastMonth = $akhirBulanLalu->toDateString();
        $data = Barang::where('id', request('id'))
            ->with([
                'stokAwal' => function ($q) use ($lastMonth) {
                    $q->where('jumlah_k', '>', 0)
                        ->whereDate('tgl_opname', $lastMonth);
                },
                'stok' => function ($q) {
                    $q->where('jumlah_k', '>', 0);
                },
                'penyesuaian' => function ($q) use ($awalBulan, $akhirBulan) {
                    $q->whereBetween('tgl_penyesuaian', [$awalBulan, $akhirBulan]);
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
            ])
            ->first();
        return new JsonResponse([
            'data' => $data,
            'req' => request()->all(),
        ]);
    }
}
