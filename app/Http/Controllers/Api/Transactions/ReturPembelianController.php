<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Penerimaan_h;
use App\Models\Transactions\ReturPembelian_h;
use App\Models\Transactions\ReturPembelian_r;
use App\Models\Transactions\Stok;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturPembelianController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = ReturPembelian_h::query()
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('noretur', 'like', '%' . request('q') . '%')
                        ->orWhere('nopenerimaan', 'like', '%' . request('q') . '%')
                        ->orWhere('nofaktur', 'like', '%' . request('q') . '%');
                });
            })
            ->with([
                'rincian' => function ($query) {
                    $query->with(['barang']);
                },
                'suplier',
            ])
            ->select('retur_pembelian_hs.*')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function getpenerimaan()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = Penerimaan_h::query()
            ->where('nopenerimaan', request('nopenerimaan'))
            ->with([
                'rincian' => function ($query) {
                    $query->with(['barang']);
                },
                'suplier',
            ])
           ->where('flag', '1')
           ->get();
        return new JsonResponse([
            'data' => $query
        ],200);
    }

    public function simpan(Request $request)
    {

        $validated = $request->validate([
            'noretur' => 'nullable',
            'nopenerimaan' => 'required',
            'nofaktur' => 'required',
            'tglretur' => 'required',
            'kode_supplier' => 'required',
            'kode_barang' => 'required',
            'nobatch' => 'required',
            'id_penerimaan_rinci' => 'required',
            'isi' => 'required',
            'satuan_k' => 'required',
            'satuan_b' => 'required',
            'jumlah_b' => 'required',
            'jumlah_k' => 'required',
            'harga_b' => 'required',
            'harga' => 'required',
            'pajak_rupiah' => 'nullable',
            'pajak' => 'nullable',
            'diskon_persen' => 'nullable',
            'diskon_rupiah' => 'nullable',
            'tgl_exprd' => 'required',
            'harga_total' => 'required',
            'subtotal' => 'required',
            'jenispajak' => 'required',
            'jumlahretur_b' => 'required',


        ], [
            'nopenerimaan.required' => 'No. Penerimaan Harus Di isi.',
            'tglretur.required' => 'Tgl Retur Harus Di isi.',
            'nofaktur.required' => 'No. Faktur Harus Di isi.',
            'tgl_faktur.required' => 'Tgl Faktur Harus Di isi.',
            'kode_supplier.required' => 'Kode Supplier Harus Di isi.',
            'kode_barang.required' => 'Kode Barang Harus Di isi.',
            'nobatch.required' => 'No. Batch Harus Di isi.',
            'id_penerimaan_rinci.required' => 'id Rincian Penerimaan belum di ikutkan, silahkan kontak penyedia IT',
            'isi.required' => 'Isi per Satuan Besar Barang Harus Di isi.',
            'tgl_exprd.required' => 'Tanggal Expired Harus Di isi.',
            'satuan_k.required' => 'Satuan Kecil Harus Di isi.',
            'satuan_b.required' => 'Satuan Besar Harus Di isi.',
            'jumlah_b.required' => 'Jumlah Satuan Besar Harus Di isi.',
            'jumlah_k.required' => 'Jumlah Satuan Kecil Harus Di isi.',
            'harga_b.required' => 'Harga Harus Di isi.',
            'jenispajak.required' => 'Jenis Pajak Harus Di isi.',
            'jumlahretur_b.required' => 'Jumlah Retur Harus Di isi.',
            'harga.required' => 'Harga Harus Di isi.',
        ]);

        try{
            DB::beginTransaction();
                $jumlahretur_k = 0;
                $cekstok = 0;
                $stoksekarang = 0;
                $sisastok_k = 0;
                $jumlahretur_k = (int) $validated['jumlahretur_b'] * (int) $validated['isi'];

                    $cekstok = Stok::where('id_penerimaan_rinci', $request->id_penerimaan_rinci)->first();
                    $stoksekarang = $cekstok->jumlah_k;
                    $sisastok_k = $stoksekarang - $jumlahretur_k;

                    if ($jumlahretur_k > $stoksekarang) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Jumlah retur melebihi stok.',
                        ], 410);
                    }else{
                        $cekstok->update(['jumlah_k' => $sisastok_k]);
                    }



                if (!$validated['noretur']) {
                    DB::select('call noretur_penjualan(@nomor)');
                    $nomor = DB::table('counter')->select('noretur_penjualan')->first();

                    $noretur = FormatingHelper::notrans($nomor->noretur_penjualan, 'RB');
                } else {
                    $noretur = $request->noretur;

                    // Cek apakah order sudah ada dan sudah terkunci
                    $existingHeader = ReturPembelian_h::where('noretur', $noretur)->first();
                    if ($existingHeader && $existingHeader->flag == '1') {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Data Order Sudah Terkunci'
                        ], 403);
                    }
                }

                $user = Auth::user();
                $returheder = ReturPembelian_h::updateOrCreate(
                    [
                        'noretur' => $noretur,
                    ],
                    [
                        'nopenerimaan' => $request->nopenerimaan,
                        'nofaktur' => $request->nofaktur,
                        'tglretur' => $request->tglretur,
                        'kode_supplier' => $request->kode_supplier,
                        'kode_user' => $user->kode,
                    ]
                );
                if (!$returheder) {
                    throw new \Exception('Gagal menyimpan Header Retur.');
                }

                if($validated['jenispajak'] === 'Exclude'){
                    $pajakretur_rupiah = (int) $validated['harga_b'] * ($validated['pajak'] / 100);
                }else{
                    $pajakretur_rupiah = 0;
                }
                if (isset($validated['diskon_persen'])) {
                    $diskonretur_rupiah = (int) $validated['harga_b'] * ($validated['diskon_persen'] / 100);
                }else{
                    $diskonretur_rupiah = 0;
                }
                $hargaretur_total = $validated['harga_b'] + $pajakretur_rupiah - $diskonretur_rupiah;
                $subtotalretur = $hargaretur_total * $validated['jumlahretur_b'];
                $returrinci = ReturPembelian_r::updateOrCreate(
                    [
                        'noretur' => $noretur,
                        'kode_barang' => $validated['kode_barang'],
                        'nobatch' => $validated['nobatch'],
                    ],
                    [
                        'id_penerimaan_rinci' => $validated['id_penerimaan_rinci'],
                        'isi' => $validated['isi'],
                        'satuan_b' => $validated['satuan_b'],
                        'satuan_k' => $validated['satuan_k'],
                        'jumlah_b' => $validated['jumlah_b'],
                        'jumlah_k' => $validated['jumlah_k'],
                        'harga_b' => $validated['harga_b'],
                        'harga' => $validated['harga'],
                        'pajak_rupiah' => $request->pajak_rupiah,
                        'diskon_persen' => $validated['diskon_persen'],
                        'diskon_rupiah' => $request->diskon_rupiah,
                        'harga_total' => $request->harga_total,
                        'subtotal' => $request->subtotal,
                        'tgl_exprd' => $validated['tgl_exprd'],
                        'jumlahretur_b' => $validated['jumlahretur_b'],
                        'jumlahretur_k' => $jumlahretur_k,
                        'diskonretur_rupiah' => $diskonretur_rupiah,
                        'pajakretur_rupiah' => $pajakretur_rupiah,
                        'hargaretur_total' => $hargaretur_total,
                        'subtotalretur' => $subtotalretur,
                        'kode_user' => $user->kode,
                    ]
                );
                if (!$returrinci) {
                    throw new \Exception('Gagal menyimpan Rincian Retur.');
                }
                DB::commit();
                    $returheder->load([
                        'rincian' => function ($query) {
                            $query->with(['barang']);
                        },
                        'suplier',
                    ]);
                    return new JsonResponse([
                        'success' => true,
                        'data' => $returheder,
                        'message' => 'Data Retur Pembelian berhasil disimpan'
                    ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
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
        $cek = ReturPembelian_h::where('noretur', $request->noretur)->where('flag', '1')->count();
        if ($cek > 0) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini sudah terkunci.'
            ], 410);
        }

        try {
            DB::beginTransaction();
            // Hapus order records
            $cari = ReturPembelian_r::where('id_penerimaan_rinci', $request->id_penerimaan_rinci)->where('id', $request->id)->first();
            $stok = Stok::where('id_penerimaan_rinci', $request->id_penerimaan_rinci)->first();

            $sisastok = $stok->jumlah_k + $cari->jumlahretur_k;

            $stok->update(['jumlah_k' => $sisastok]);
            ReturPembelian_r::where('id', $request->id)->delete();

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'message' => 'Data Retur berhasil dihapus'
            ]);
        }catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 410);
        }
    }

    public function lock_retur_pembelian(Request $request)
    {
        $validated = $request->validate([
            'noretur' => 'required',
        ], [
            'noretur.required' => 'Nomor Retur harus diisi.',
        ]);

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        $existingHeader = ReturPembelian_h::where('noretur', $validated['noretur'])->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur Tidak Ditemukan.'
            ], 410);
        }

        if ($existingHeader && $existingHeader->flag == '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini sudah terkunci.'
            ], 410);
        }

        try {
            DB::beginTransaction();
            $returHeader = ReturPembelian_h::where('noretur', $validated['noretur'])->first();

            if (!$returHeader) {
                throw new \Exception('Gagal mengunci data retur.');
            }

            // Update header
            $returHeader->update(['flag' => '1']); // Lock Table

            DB::commit();

            $returData = ReturPembelian_h::with([
                'rincian.barang:nama,kode,satuan_k,satuan_b,isi,kandungan',
            ])->find($existingHeader->id);

            return new JsonResponse([

                'data' => $returData,
                'message' => 'Data Retur Pembelian berhasil dikunci'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunci data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => Auth::user(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
}
