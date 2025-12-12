<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use App\Models\Setting\ProfileToko;
use App\Models\Transactions\StokOpname;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StokOpnameController extends Controller
{
    //
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
            'bulan' => request('bulan') ?? Carbon::now()->month,
            'tahun' => request('tahun') ?? Carbon::now()->year,
        ];
        $tglOpname = Carbon::create($req['tahun'], $req['bulan'])->endOfMonth()->toDateString();
        $query = StokOpname::query()
            ->leftjoin('barangs', 'stok_opnames.kode_barang', '=', 'barangs.kode')
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('barangs.nama', 'like', '%' . request('q') . '%');
                });
            })
            ->with([
                'barang'
            ])
            ->whereDate('tgl_opname', $tglOpname)
            ->select('stok_opnames.*')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required',
            'bulan' => 'required',
        ], [
            'tahun.required' => 'tahun opname harus diisi.',
            'bulan.required' => 'bulan opname harus diisi.',
        ]);
        $now = Carbon::now()->startOfMonth();
        $target = Carbon::create($validated['tahun'], $validated['bulan'], 1);
        if (!$target->lt($now)) {
            return new JsonResponse(
                ['message' => 'Transaksi Opname hanya bisa di lakukan di bulan lalu'],
                410
            );
        }
        $opnameTerakhir = StokOpname::select('tgl_opname')->orderBy('tgl_opname', 'desc')->first();
        $tglOpnameTerakhir = $opnameTerakhir->tgl_opname ?? null;
        // $akhirBulanLalu = Carbon::create($validated['tahun'], $validated['bulan'])->subMonth(1)->endOfMonth()->toDateString() . ' 23:59:59';
        $akhirBulanLalu = Carbon::create($validated['tahun'], $validated['bulan'])->endOfMonth()->toDateString() . ' 23:59:59'; // diambil bulan query 
        if ($tglOpnameTerakhir == $akhirBulanLalu) return new JsonResponse(['message' => 'Sudah ada opname di tanggal yang sama'], 410);

        /**
         *  ------ rules -----
         * kalo ada tgl opname ambil transaksi dari setelah tgl opname sampai sebelum bulan lalu
         * kalo ga ada tgl opname ambil semua transaksi sebelum akhir bulan lalu
         * cek transaksi nya diambil dari rincian penerimaan id pada kode obta tsb. rincian penerimaan bisa jadi ada sisa dari stok opname sebelum nya
         */
        $data = [];
        $all = [];
        $items = [];
        $itemDepos = [];
        // step 1 ambil barang
        $barang = Barang::select('kode', 'nama', 'satuan_k', 'satuan_b')
            ->with([
                'stok',
                'stokAwal' => function ($q) use ($tglOpnameTerakhir) {
                    $q->when($tglOpnameTerakhir, function ($y) use ($tglOpnameTerakhir) {
                        $tgl = Carbon::create($tglOpnameTerakhir)->toDateString();
                        $y->whereDate('tgl_opname', $tgl);
                    });
                },
                'penerimaanRinci' => function ($q) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                    $q->select(
                        'penerimaan_rs.id',
                        'penerimaan_rs.kode_barang',
                        'penerimaan_rs.nopenerimaan',
                        'penerimaan_rs.jumlah_k',
                        'penerimaan_rs.jumlah_b',
                        'penerimaan_rs.nobatch',
                        'penerimaan_rs.tgl_exprd',
                        'penerimaan_rs.isi',
                        'penerimaan_rs.satuan_b',
                        'penerimaan_rs.satuan_k',
                        'penerimaan_rs.harga_total', // apakah ini harga satuan kecil?
                        'penerimaan_rs.harga', // apakah ini harga satuan kecil?
                    )
                        ->leftJoin('penerimaan_hs', 'penerimaan_hs.nopenerimaan', '=', 'penerimaan_rs.nopenerimaan')
                        ->when($tglOpnameTerakhir, function ($y) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                            $y->whereBetween('penerimaan_hs.tgl_penerimaan',  [$tglOpnameTerakhir, $akhirBulanLalu]);
                        }, function ($y) use ($akhirBulanLalu) {
                            $y->whereDate('penerimaan_hs.tgl_penerimaan', '<=', $akhirBulanLalu);
                        })
                        ->whereNotNull('penerimaan_hs.flag');
                },
                'penjualanRinci' => function ($q) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                    $q->select(
                        // 'penjualan_r_s.id_penerimaan_rinci',
                        'penjualan_r_s.kode_barang',
                        'penjualan_r_s.nopenerimaan',
                        'penjualan_r_s.jumlah_k',
                        'penjualan_r_s.harga_beli',
                    )
                        ->leftJoin('penjualan_h_s', 'penjualan_h_s.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
                        ->when($tglOpnameTerakhir, function ($y) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                            $y->whereBetween('penjualan_h_s.tgl_penjualan',  [$tglOpnameTerakhir, $akhirBulanLalu]);
                        }, function ($y) use ($akhirBulanLalu) {
                            $y->whereDate('penjualan_h_s.tgl_penjualan', '<=', $akhirBulanLalu);
                        })
                        ->whereNotNull('penjualan_h_s.flag');
                },
                'returPenjualanRinci' => function ($q) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                    $q->select(
                        // 'retur_penjualan_rs.id_penerimaan_rinci',
                        'retur_penjualan_rs.kode_barang',
                        'retur_penjualan_rs.nopenerimaan',
                        'retur_penjualan_rs.jumlah_k',
                        'retur_penjualan_rs.harga',
                    )
                        ->leftJoin('retur_penjualan_hs', 'retur_penjualan_hs.noretur', '=', 'retur_penjualan_rs.noretur')
                        ->when($tglOpnameTerakhir, function ($y) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                            $y->whereBetween('retur_penjualan_hs.tgl_retur',  [$tglOpnameTerakhir, $akhirBulanLalu]);
                        }, function ($y) use ($akhirBulanLalu) {
                            $y->whereDate('retur_penjualan_hs.tgl_retur', '<=', $akhirBulanLalu);
                        })
                        ->whereNotNull('retur_penjualan_hs.flag');
                },
                'returPembelianRinci' => function ($q) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                    $q->select(
                        // 'retur_pembelian_rs.id_penerimaan_rinci',
                        'retur_pembelian_rs.kode_barang',
                        'retur_pembelian_rs.jumlah_k',
                        'retur_pembelian_rs.harga',
                    )
                        ->leftJoin('retur_pembelian_hs', 'retur_pembelian_hs.noretur', '=', 'retur_pembelian_rs.noretur')
                        ->when($tglOpnameTerakhir, function ($y) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                            $y->whereBetween('retur_pembelian_hs.tglretur',  [$tglOpnameTerakhir, $akhirBulanLalu]);
                        }, function ($y) use ($akhirBulanLalu) {
                            $y->whereDate('retur_pembelian_hs.tglretur', '<=', $akhirBulanLalu);
                        })
                        ->whereNotNull('retur_pembelian_hs.flag');
                },
                'penyesuaian' => function ($q) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                    $q->select(
                        'id_stok',
                        'kode_barang',
                        'jumlah_k',
                    )
                        ->when($tglOpnameTerakhir, function ($y) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                            $y->whereBetween('tgl_penyesuaian',  [$tglOpnameTerakhir, $akhirBulanLalu]);
                        }, function ($y) use ($akhirBulanLalu) {
                            $y->whereDate('tgl_penyesuaian', '<=', $akhirBulanLalu);
                        });
                },
                'mutasiMasuk' => function ($q) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                    $q->select(
                        'mutasi_requests.kode_barang',
                        'mutasi_requests.jumlah',
                        'mutasi_requests.distribusi',
                        'mutasi_headers.dari',
                        'mutasi_headers.tujuan',
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->when($tglOpnameTerakhir, function ($y) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                            $y->whereBetween('mutasi_headers.tgl_terima',  [$tglOpnameTerakhir, $akhirBulanLalu]);
                        }, function ($y) use ($akhirBulanLalu) {
                            $y->whereDate('mutasi_headers.tgl_terima', '<=', $akhirBulanLalu);
                        })
                        ->whereNotNull('mutasi_headers.status');
                },
                'mutasiKeluar' => function ($q) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                    $q->select(
                        'mutasi_requests.kode_barang',
                        'mutasi_requests.jumlah',
                        'mutasi_requests.distribusi',
                        'mutasi_headers.dari',
                        'mutasi_headers.tujuan',
                    )
                        ->leftJoin('mutasi_headers', 'mutasi_headers.kode_mutasi', '=', 'mutasi_requests.kode_mutasi')
                        ->when($tglOpnameTerakhir, function ($y) use ($tglOpnameTerakhir, $akhirBulanLalu) {
                            $y->whereBetween('mutasi_headers.tgl_distribusi',  [$tglOpnameTerakhir, $akhirBulanLalu]);
                        }, function ($y) use ($akhirBulanLalu) {
                            $y->whereDate('mutasi_headers.tgl_distribusi', '<=', $akhirBulanLalu);
                        })
                        ->whereNotNull('mutasi_headers.status');
                }
            ])
            ->get();
        $profile = ProfileToko::first();
        foreach ($barang as $key) {
            // dibedakan antara gudang dan depo
            // gudang

            $stok = $key->stok ? $key->stok->firstWhere('kode_depo', 'APS0000') : null;
            $items[] = $stok;
            $stokAwalGud = !empty($key->stok_awal) ? $key->stok_awal->where('kode_depo', 'APS0000')->sum('jumlah_k') : 0;
            $penerimaan = $key->penerimaanRinci ? $key->penerimaanRinci->sum('jumlah_k') : 0;
            $returPembelian = $key->returPembelianRinci ? $key->returPembelianRinci->sum('jumlah_k') : 0;
            $mutasiMasuk = $key->mutasiMasuk ? $key->mutasiMasuk->where('dari', 'APS0000')->sum('jumlah_k') : 0;
            $mutasiKeluar = $key->mutasiMasuk ? $key->mutasiMasuk->where('tujuan', 'APS0000')->sum('jumlah_k') : 0;
            $penyesuaian = $key->penyesuaian && $stok ? $key->penyesuaian->where('id_stok', $stok->id)->sum('jumlah_k') : 0;
            // if (empty($key->stok_awal)) {
            //     $sisa = (int) $stok->jumlah_k;
            // } else {
            $sisa = (int)$stokAwalGud + (int)$penerimaan + (int)$mutasiMasuk + (int)$penyesuaian - (int)$mutasiKeluar - (int)$returPembelian;
            // }
            if ($stok && $sisa > 0) {
                $data[] = [
                    'kode_depo' => 'APS0000',
                    'kode_barang' => $key->kode,
                    'satuan_k' => $stok->satuan_k,
                    'jumlah_k' => $sisa,
                    'tgl_opname' => $akhirBulanLalu,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ];
            }
            // depo
            $depo = $profile->kode_toko;

            $stokdepo = $key->stok ? $key->stok->firstWhere('kode_depo', $depo) : null;
            $itemDepos[] = $stokdepo;
            $stokAwal = !empty($key->stok_awal) ? $key->stok_awal->where('kode_depo', $depo)->sum('jumlah_k') : 0;
            $penjualan = $key->penjualanRinci ? $key->penjualanRinci->sum('jumlah_k') : 0;
            $returPenjualan = $key->returPenjualanRinci ? $key->returPenjualanRinci->sum('jumlah_k') : 0;
            $mutasiMasuk = $key->mutasiMasuk ? $key->mutasiMasuk->where('dari', $depo)->sum('jumlah_k') : 0;
            $mutasiKeluar = $key->mutasiMasuk ? $key->mutasiMasuk->where('tujuan', $depo)->sum('jumlah_k') : 0;

            $penyesuaian = $key->penyesuaian && $stok  ? $key->penyesuaian->where('id_stok', $stok->id)->sum('jumlah_k') : 0;
            // if (empty($key->stok_awal)) {
            //     $sisadepo = (int) $stok->jumlah_k;
            // } else {
            $sisadepo = (int)$stokAwal + (int)$mutasiMasuk + (int)$returPenjualan + (int)$penyesuaian - (int)$penjualan - (int)$mutasiKeluar;
            // }
            if ($stokdepo && $sisadepo > 0) {
                $data[] = [
                    'kode_depo' => $depo,
                    'kode_barang' => $key->kode,
                    'satuan_k' => $stokdepo->satuan_k,
                    'jumlah_k' => $sisadepo,
                    'tgl_opname' => $akhirBulanLalu,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ];
            }
        }
        if (count($data) <= 0) {
            return new JsonResponse([
                'message' => 'Tidak ada Data untuk di simpan. apakah ada transaksi di bulan tersebut?',
                // 'depo' => $depo,
                // 'items' => $items,
                // 'itemDepos' => $itemDepos,
                // 'all' => $all,
                // 'barang' => $barang
            ], 410);
        }
        $hasil = collect($data)->chunk(1000)->each(function ($chunk) {
            StokOpname::insert($chunk->toArray());
        });
        // $hasil = StokOpname::insert($data);
        return new JsonResponse([
            'hasil' => $hasil ?? null,
            'data' => $data ?? null,
            'barang' => $barang,
            'tglOpnameTerakhir' => $tglOpnameTerakhir,
            'akhirBulanLalu' => $akhirBulanLalu,
        ]);
    }
    public static function opnameOtomatis()
    {

        $today =  date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 days'));
        // $yesterday = date('Y-m-d', strtotime('-14 days'));
        $lastDay = date('Y-m-01', strtotime($today));
        $dToday = date_create($today);
        $dLastDay = date_create($lastDay);
        $diff = date_diff($dToday, $dLastDay);

        $request = new Request([
            'tahun' => date('Y', strtotime($yesterday)),
            'bulan' => date('m', strtotime($yesterday))
        ]);
        if ($diff->d === 0 && $diff->m === 0) {
            $instance = new self;
            $data = $instance->simpan($request);
            return $data;
        }

        return new JsonResponse([
            'message' => 'Stok opname farmasi dapat dilakukan di hari terakhir tiap bulan',
            'hari ini' => $yesterday
        ], 410);
    }
}
