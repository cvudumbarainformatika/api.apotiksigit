<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use App\Models\Setting\ProfileToko;
use App\Models\Transactions\PenjualanH;
use App\Models\Transactions\PenjualanR;
use App\Models\Transactions\Stok;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    //
    public function getListObat(): JsonResponse
    {
        $req = [
            'order_by' => request('order_by') ?? 'nama',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];
        $limitHargaTertinggi = 5;
        $profile = ProfileToko::first();
        // tambah penjualan yang belum selesai -- ibarat alokasi... maping di front
        $data = Barang::when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->with([
                'stok' => function ($q) use ($profile) {
                    $q->where('kode_depo', $profile->kode_toko);
                },
                'penjualanRinci' => function ($q) {
                    $q->select(
                        'kode_barang',
                        DB::raw('sum(jumlah_k) as jumlah_k'),
                        'id_stok',
                    )
                        ->leftJoin('penjualan_h_s', 'penjualan_h_s.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
                        ->whereNull('penjualan_h_s.flag')
                        ->groupBy('kode_barang', 'id_stok');
                }
            ])
            // ->addSelect([
            //     'harga_tertinggi_ids' => Stok::query()
            //         ->selectRaw("
            //     SUBSTRING_INDEX(
            //         GROUP_CONCAT(stoks.id ORDER BY stoks.id DESC SEPARATOR ','),
            //         ',',
            //         {$limitHargaTertinggi}
            //     )
            // ")
            //         ->whereColumn('stoks.kode_barang', '=', 'barangs.kode')
            // ])
            ->orderBy($req['order_by'], $req['sort'])
            ->limit($req['per_page'])
            ->get();

        // $stokIds = $data->pluck('harga_tertinggi_ids')
        //     ->filter() // buang null
        //     ->map(fn($ids) => explode(',', $ids))
        //     ->flatten();

        // $stokHargaTertinggi = Stok::select('id', 'kode_barang', 'harga_total')
        //     ->whereIn('id', $stokIds)
        //     ->get();
        // foreach ($data as $barang) {
        //     $ids = $barang->harga_tertinggi_ids ? explode(',', $barang->harga_tertinggi_ids) : [];

        //     $barangHargaTertinggi = $stokHargaTertinggi
        //         ->whereIn('id', $ids)
        //         ->sortBy(fn($row) => array_flip($ids)[$row->id])
        //         ->values();

        //     // set relasi semu "harga_tertinggi"
        //     // $barang->setRelation('harga_tertinggi', $barangHargaTertinggi);

        //     // langsung hitung max harga_total
        //     $barang->hpp = $barangHargaTertinggi->max('harga_total');
        // }
        return new JsonResponse([
            'data' => $data
        ]);
    }
    public function index(): JsonResponse
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => request('from') ?? null,
            'to' => request('to') ?? null,
        ];

        $raw = PenjualanH::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->when($req['from'], function ($q) use ($req) {
                $q->whereBetween('tgl_penjualan', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59']);
            })
            ->with([
                'rinci.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ])
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function simpan(Request $request): JsonResponse
    {
        $nomorPen = $request->nopenjualan;
        $validated = $request->validate([

            'tgl_penjualan' => 'nullable',
            'kode_pelanggan' => 'nullable',
            'kode_dokter' => 'nullable',

            'kode_barang' => 'required',
            'jumlah_k' => 'required',
            'satuan_k' => 'nullable',
            'satuan_b' => 'nullable',
            'diskon' => 'nullable',
            'isi' => 'required',
            'harga_jual' => 'required', // ini dari master
            'harga_beli' => 'required', // ini dari master
            // 'hpp' => 'required', // ini di taruh di master, hasil query dari 5 harga terakhir
            'hpp' => 'nullable', // ini di taruh di master, hasil query dari 5 harga terakhir
            // 'id_penerimaan_rinci' => 'required', // ini dari stok
            // 'nopenerimaan' => 'required', // ini dari stok
            // 'nobatch' => 'required', // ini dari stok
            // 'tgl_exprd' => 'required', // ini dari stok
            'id_stok' => 'required', // ini dari stok
        ], [
            'kode_barang.required' => 'Kode Barang Harus Di isi.',
            'jumlah_k.required' => 'Jumalah Barang Harus Di isi.',
            'isi.required' => 'Isi per Satuan Besar Barang Harus Di isi.',
            'harga_jual.required' => 'Harga Jual Harus Di isi.',
            'harga_beli.required' => 'Harga Beli Harus Di isi.',
            // 'id_penerimaan_rinci.required' => 'id Rincian Penerimaan belum di ikutkan, silahkan kontak penyedia IT',
            // 'nopenerimaan.required' => 'Nomor Penerimaan belum di ikutkan, silahkan kontak penyedia IT',
            // 'nobatch.required' => 'Nomor Batch belum di ikutkan, silahkan kontak penyedia IT',
            // 'tgl_exprd.required' => 'Tanggal Expired Obat di ikutkan, silahkan kontak penyedia IT',
            'id_stok.required' => 'id Stok belum di ikutkan, silahkan kontak penyedia IT',
            // 'hpp.required' => 'HPP belum di ikutkan, silahkan kontak penyedia IT',
        ]);
        try {
            DB::beginTransaction();
            $user = Auth::user();
            if (!$nomorPen) {
                DB::select('call nopenjualan(@nomor)');
                $nomor = DB::table('counter')->select('nopenjualan')->first();
                $nopenjualan = FormatingHelper::genKodeBarang($nomor->nopenjualan, 'TRX');
            } else {
                $nopenjualan = $request->nopenjualan;
            }
            $jumlahB = floor($validated['jumlah_k'] / $validated['isi']);
            $subtotal = $validated['jumlah_k'] * $validated['harga_jual'];
            $data = PenjualanH::updateOrCreate([
                'nopenjualan' => $nopenjualan
            ], [
                'tgl_penjualan' => $validated['tgl_penjualan'] ?? Carbon::now()->format('Y-m-d H:i:s'),
                'kode_pelanggan' => $validated['kode_pelanggan'],
                'kode_dokter' => $validated['kode_dokter'],
                'kode_user' => $user->kode,
                'cara_bayar' => '',
            ]);
            if (!$data) {
                throw new \Exception('Data Penjualan Gagal Disimpan.');
            }
            $rinci = PenjualanR::updateOrCreate([
                'nopenjualan' => $nopenjualan,
                'kode_barang' => $validated['kode_barang'],
                'id_penerimaan_rinci' => '',
                'id_stok' => $validated['id_stok'],
                'jumlah_k' => $validated['jumlah_k'],
            ], [
                'jumlah_b' => $jumlahB,
                'nopenerimaan' => '',
                'nobatch' => '',
                'isi' => $validated['isi'],
                'satuan_k' => $validated['satuan_k'],
                'satuan_b' => $validated['satuan_b'],
                'tgl_exprd' => null,
                'harga_jual' => $validated['harga_jual'],
                'harga_beli' => $validated['harga_beli'],
                'diskon' => $validated['diskon'] ?? 0,
                'hpp' => $validated['hpp'] ?? $validated['harga_beli'],
                'subtotal' => $subtotal,
                'kode_user' => $user->kode,
            ]);
            if (!$rinci) {
                throw new \Exception('Data Penjualan Gagal Disimpan.');
            }
            DB::commit();
            $data->load([
                'rinci.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ]);
            return new JsonResponse([
                'message' => 'Data berhasil disimpan',
                'data' => $data,
                'rinci' => $rinci,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => Auth::user(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
    public function bayar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required',
            'diskon' => 'nullable',
            'jumlah_bayar' => 'required',
            'kembali' => 'nullable',
            'cara_bayar' => 'required',
        ], [
            'cara_bayar.required' => 'Cara Bayar Harus Di isi.',
            'id.required' => 'Id Header Penjualan Harus Di isi.',
            'jumlah_bayar.required' => 'Jumlah Bayar Harus Di isi.',
        ]);
        $user = Auth::user();
        try {
            DB::beginTransaction();
            $data = PenjualanH::find($validated['id']);
            if (!$data) throw new \Exception('Data Penjualan Tidak Ditemukan.');

            if ($data->flag == 1) throw new \Exception('Transaksi penjualan ini sudah dibayar.');

            // hitung Subtotal
            $subtotal = PenjualanR::where('nopenjualan', $data->nopenjualan)->sum('subtotal');

            $diskon = $validated['diskon'] ?? 0;
            $kembali = $validated['kembali'] ?? 0;
            $jumlahBayar = $validated['jumlah_bayar'] ?? 0;
            // tentkan jumlah pembayran jika ada diskon dan tidak
            if ($diskon > 0) $nilaiBayar = (int)$subtotal - (int)$diskon;
            else $nilaiBayar = (int)$subtotal;
            // validasi jumlah pembayaran
            if ((int)$jumlahBayar < (int)$nilaiBayar) {
                throw new Exception('Jumlah Pembayaran kurang, minimal ' . $nilaiBayar);
            }
            $nilaiKelbalian = (int)$jumlahBayar - (int)$nilaiBayar;
            // validasi kembalian
            if ($kembali != $nilaiKelbalian) $kembali = (int)$jumlahBayar - (float)$nilaiBayar;
            else $kembali = $validated['kembali'];
            // update data
            $data->update([
                'cara_bayar' => $validated['cara_bayar'],
                'diskon' => $diskon,
                'jumlah_bayar' => $jumlahBayar,
                'kembali' => $kembali,
                'kode_user' => $user->kode,
                'flag' => '1'
            ]);
            // kurangi stok di tabel stok
            $rincian = PenjualanR::where('nopenjualan', $data->nopenjualan)->get();
            foreach ($rincian as $rinci) {
                $stok = Stok::find($rinci->id_stok);
                // validasi sisa stok agar tidak minus
                // if ((int)$stok->jumlah_k < (int)$rinci->jumlah_k) {
                //     $nama = Barang::where('kode', $rinci->kode_barang)->first();
                //     throw new Exception('Stok ' . $nama->nama . ' tgl expired ' . $stok->tgl_exprd . ' kurang, sisa stok sejumlah ' . (int)$stok->jumlah_k);
                // }
                $jumlah = (int)$stok->jumlah_k - (int)$rinci->jumlah_k;
                $stok->update([
                    'jumlah_k' => $jumlah,
                ]);
            }
            DB::commit();
            $data->load([
                'rinci.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ]);
            return new JsonResponse([
                'message' => 'Pembayaran berhasil dilakukan',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => Auth::user(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }

    public function hapus(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required',
            'nopenjualan' => 'required',
        ], [
            'kode_barang.required' => 'Tidak Ada Rincian untuk dihapus',
            'nopenjualan.required' => 'Nomor Transaksi Harus di isi',
        ]);

        try {
            DB::beginTransaction();
            $msg = 'Rincian Obat sudah dihapus';
            $rinci = PenjualanR::where('nopenjualan', $validated['nopenjualan'])->where('kode_barang', $validated['kode_barang'])->get();
            if (count($rinci) == 0) throw new \Exception('Data Obat Tidak Ditemukan.');
            $nopenjualan = $validated['nopenjualan'];
            $header = PenjualanH::where('nopenjualan', $nopenjualan)->first();
            if (!$header) throw new \Exception('Data header tidak ditemukan.');
            if ($header->flag !== null) throw new Exception('Data sudah terkunci, tidak boleh dihapus');

            // hitung sisa rincian
            foreach ($rinci as $r) {
                $r->delete();
            }
            $sisaRinci = PenjualanR::where('nopenjualan', $nopenjualan)->count();
            if ($sisaRinci == 0) {
                $header->delete();
                $msg = 'Semua rincian dihapus, data header juga dihapus';
            }
            DB::commit();
            return new JsonResponse([
                'message' => $msg
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' =>  $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
}
