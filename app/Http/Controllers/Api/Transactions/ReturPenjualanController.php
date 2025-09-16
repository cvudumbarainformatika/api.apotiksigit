<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use App\Models\Transactions\ReturPenjualan_h;
use App\Models\Transactions\ReturPenjualan_r;
use App\Models\Transactions\Penerimaan_h;
use App\Models\Transactions\PenjualanH;
use App\Models\Transactions\PenjualanR;
use App\Models\Transactions\Stok;
use Carbon\Carbon;
use Date;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturPenjualanController extends Controller
{
    /*
     * FLAG
     * null = Belum Terkunci
     * 1 = Terkunci
     */

    public function index(Request $request)
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = ReturPenjualan_h::query()
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $kode = Barang::select('kode')->where('nama', 'like', '%' . request('q') . '%')->pluck('kode');
                    $query->where('noretur', 'like', '%' . request('q') . '%')
                        ->when(count($kode) > 0, function ($q) use ($kode) {
                            $rinci = ReturPenjualan_r::select('noretur')
                                ->distinct()
                                ->whereIn('kode_barang', $kode)
                                ->pluck('noretur');
                            $q->orWhereIn('noretur', $rinci);
                        });
                });
            })
            ->with([
                'returPenjualan_r.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ])
            ->orderBy('retur_penjualan_hs.' . $req['order_by'], $req['sort']);

        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function getTransaksiPenjualan()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        // $query = PenjualanH::query()
        //     ->when(request('q'), function ($q) {
        //         $q->where('penjualan_h_s.nopenjualan', '=',  request('q'));
        //         // $kode = Barang::select('kode')->where('nama', 'like', '%' . request('q') . '%')->pluck('kode');
        //         // $q->where(function ($query) use ($kode) {
        //         //     $query->where('penjualan_h_s.nopenjualan', 'like', '%' . request('q') . '%');
        //         // ->when(count($kode) > 0, function ($q) use ($kode) {
        //         //     $rinci = PenjualanR::select('penjualan_r_s.nopenjualan')
        //         //         ->distinct()
        //         //         ->leftJoin('penjualan_h_s', 'penjualan_h_s.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
        //         //         ->whereIn('kode_barang', $kode)
        //         //         ->where('flag', '1')
        //         //         ->pluck('nopenjualan');
        //         //     $q->orWhereIn('nopenjualan', $rinci);
        //         // });
        //         // });
        //     })
        //     ->with([
        //         'rinci.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
        //     ])
        //     ->orderBy('penjualan_h_s.' . $req['order_by'], $req['sort']);

        // $totalCount = (clone $query)->count();
        // $data = $query->simplePaginate($req['per_page']);

        // $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        // return new JsonResponse($resp);

        $data = PenjualanH::where('nopenjualan', request('q'))
            ->with([
                'rinci.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ])
            ->get();
        if (count($data)) {
            // cek rincian retur
            $rincianret = ReturPenjualan_r::where('nopenjualan', $data[0]->nopenjualan)->get();
            if (count($rincianret) > 0) return new JsonResponse(['message' => 'Data retur sudah ada silahkan di cek di list retur'], 410);
        }
        return new JsonResponse([
            'data' => $data
        ]);
    }
    public function simpan(Request $request)
    {
        $noretur = $request->noretur;
        $validated = $request->validate([

            'tgl_retur' => 'nullable',
            'nopenjualan' => 'required',
            'nopenerimaan' => 'required',
            'kode_barang' => 'required',
            'nobatch' => 'required',
            'jumlah_k' => 'required',
            'satuan_k' => 'required',
            'harga' => 'required',
            'harga_beli' => 'required',
            'hpp' => 'required',
            'id_stok' => 'required',
            'id_penerimaan_rinci' => 'required',
        ], [
            'nopenjualan.required' => 'Nomor Penerimaan harus diisi.',
            'nopenerimaan.required' => 'Nomor Faktur harus diisi.',
            'kode_barang.required' => 'Kode Barang harus diisi.',
            'nobatch.required' => 'Nomor Batch harus diisi.',
            'jumlah_k.required' => 'Jumlah harus diisi.',
            'satuan_k.required' => 'Satuan harus diisi.',
            'harga.required' => 'Harga harus diisi.',
            'harga_beli.required' => 'Harga Beli harus diisi.',
            'hpp.required' => 'HPP harus diisi.',
            'id_stok.required' => 'id stok harus diisi.',
            'id_penerimaan_rinci.required' => 'id rinci penerimaan harus diisi.',
        ]);

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        if (!$noretur) {
            // cek apakah ada nopenjualan yang sudah di retur
            $headRtr = ReturPenjualan_h::where('nopenjualan', $validated['nopenjualan'])->first();
            if ($headRtr) {
                if ($headRtr->flag == null) $nomor_retur = $headRtr->noretur;
                else throw new Exception('Nomor Penjualan ini sudah pernah di retur.');
            } else {
                DB::select('call noretur(@nomor)');
                $nomor = DB::table('counter')->select('noretur')->first();
                $nomor_retur = FormatingHelper::genKodeBarang($nomor->noretur, 'RPJ');
            }
        } else {
            $nomor_retur = $noretur;

            $existingHeader = ReturPenjualan_h::where('noretur', $nomor_retur)->first();
            if ($existingHeader && $existingHeader->flag == '1') {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Data Retur Sudah Terkunci'
                ], 410);
            }
        }

        try {
            DB::beginTransaction();
            $ReturPenjualanHeader = ReturPenjualan_h::updateOrCreate(
                [
                    'noretur' => $nomor_retur,
                    'nopenjualan' => $validated['nopenjualan'],
                ],
                [
                    'kode_user' => $user->kode,
                    'tgl_retur' => $validated['tgl_retur'] ?? Carbon::now()->format('Y-m-d H:i:s'),
                ]
            );

            if (!$ReturPenjualanHeader) {
                throw new \Exception('Retur Penjualan Header Gagal Disimpan.');
            }

            $ReturPenjualanRinci = ReturPenjualan_r::updateOrCreate(
                [
                    'noretur' => $nomor_retur,
                    'kode_barang' => $validated['kode_barang'],
                    'nopenjualan' => $validated['nopenjualan'],
                ],
                [
                    'nobatch' => $validated['nobatch'],
                    'jumlah_k' => $validated['jumlah_k'],
                    'satuan_k' => $validated['satuan_k'],
                    'harga' => $validated['harga'],
                    'harga_beli' => $validated['harga_beli'],
                    'hpp' => $validated['hpp'],
                    'id_penerimaan_rinci' => $validated['id_penerimaan_rinci'],
                    'nopenerimaan' => $validated['nopenerimaan'],
                    'id_stok' => $validated['id_stok'],
                    'kode_user' => $user->kode
                ]
            );

            if (!$ReturPenjualanRinci) {
                throw new \Exception('Retur Penjualan Rinci Gagal Disimpan.');
            }

            DB::commit();

            // $ReturPenjualanHeaderResult = ReturPenjualan_h::with([
            //     'returPenjualan_r.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            // ])->find($ReturPenjualanHeader->id);
            $ReturPenjualanHeader->load([
                'returPenjualan_r.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ]);


            return new JsonResponse([
                'success' => true,
                'data' => $ReturPenjualanHeader,
                'message' => 'Data Retur Penjualan berhasil disimpan'
            ], 201);
        } catch (\Throwable $e) {
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

    // Lock Retur Penjualan Hanya untuk Data Draft
    public function lock_retur_penjualan(Request $request)
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

        $existingHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])->first();
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

        // Jika flag awalnya bukan null = tidak boleh dikunci
        if ($existingHeader && $existingHeader->flag != null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini bukan data Draft'
            ], 410);
        }

        try {
            DB::beginTransaction();

            $returHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])->first();

            if (!$returHeader) {
                throw new \Exception('Gagal mengunci data retur.');
            }
            $headerPenjualan = PenjualanH::where('nopenjualan', $returHeader->nopenjualan)->first();

            if (!$headerPenjualan) {
                throw new \Exception('Data penjualan tidak ditemukan.');
            }
            // tambah stok
            $rincian = ReturPenjualan_r::where('noretur', $validated['noretur'])->get();
            if (count($rincian) == 0) throw new \Exception('Data Rincian Retur tidak ditemukan.');
            foreach ($rincian as $key) {
                $stok = Stok::find($key->id_stok);
                if (!$stok) throw new Exception('data stok tidak ditemukan');
                $jumlahStok = (int)$stok->jumlah_k + (int)$key->jumlah_k;

                $stok->update(['jumlah_k' => $jumlahStok]);
            }
            // Update header
            $returHeader->update(['flag' => '1']); // Lock Table
            // update header penjualan
            $headerPenjualan->update(['flag' => '2']); // ganti flag biar ga bisa di retur lagi

            DB::commit();

            $returData = ReturPenjualan_h::with([
                'returPenjualan_r.master:nama,kode,satuan_k,satuan_b,isi,kandungan',
            ])->find($existingHeader->id);

            return new JsonResponse([

                'data' => $returData,
                'message' => 'Data Retur Penjualan berhasil dikunci'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([

                'message' => 'Gagal mengunci data: ' . $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    // 'trace' => $e->getTraceAsString()
                ]
            ], 410);
        }
    }

    // Open Lock Retur Penjualan
    public function open_lock_retur_penjualan(Request $request)
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

        $existingHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur Tidak Ditemukan.'
            ], 410);
        }

        if ($existingHeader && $existingHeader->flag == null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini belum terkunci.'
            ], 410);
        }

        // Jika flag bukan 1 = tidak boleh dibuka
        if ($existingHeader && $existingHeader->flag != '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini bukan data Retur Terkunci.'
            ], 410);
        }

        try {
            DB::beginTransaction();

            // Update header
            $returHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])
                ->update(['flag' => null]); // Unlock Table

            if (!$returHeader) {
                throw new \Exception('Gagal membuka kunci data retur.');
            }

            DB::commit();

            $returData = ReturPenjualan_h::with([
                'returPenjualan_r.master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'penerimaan_h',
                'supplier',
            ])->find($existingHeader->id);

            return new JsonResponse([
                'success' => true,
                'data' => $returData,
                'message' => 'Kunci Data Retur Penjualan berhasil dibuka'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal membuka kunci data: ' . $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 410);
        }
    }

    public function hapus(Request $request)
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

        $existingHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur Tidak Ditemukan.'
            ], 410);
        }

        // Tidak boleh hapus jika flag = 1 (sudah terkunci)
        if ($existingHeader->flag == '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur ini sudah terkunci dan tidak dapat dihapus.'
            ], 410);
        }

        try {
            DB::beginTransaction();

            // Hapus rincian retur
            ReturPenjualan_r::where('noretur', $validated['noretur'])->delete();

            // Hapus header retur
            $existingHeader->delete();

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'message' => 'Data retur penjualan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 410);
        }
    }
    public function hapus_rincian_tidak_dikunci(Request $request)
    {
        // $noretur = $request->noretur;
        // $kode_barang = $request->kode_barang;

        // if (!$noretur || !$kode_barang) {
        //     return new JsonResponse([
        //         'success' => false,
        //         'message' => 'Nomor retur dan kode barang harus diisi'
        //     ], 400);
        // }
        $validated = $request->validate([
            'noretur' => 'required',
            'kode_barang' => 'required',
        ], [
            'noretur.required' => 'Nomor Retur harus diisi.',
            'kode_barang.required' => 'Kode Barang harus diisi.',
        ]);

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        $existingHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur Tidak Ditemukan.'
            ], 410);
        }

        // Cek apakah header sudah terkunci
        if ($existingHeader && $existingHeader->flag == '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur ini sudah terkunci dan tidak dapat diubah.'
            ], 410);
        }

        try {
            DB::beginTransaction();

            // Cari rincian yang akan dihapus
            $rincian = ReturPenjualan_r::where('noretur', $validated['noretur'])
                ->where('kode_barang', $validated['kode_barang'])
                ->first();

            if (!$rincian) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Data rincian retur tidak ditemukan'
                ], 410);
            }

            // Hapus rincian
            $rincian->delete();
            $rincianAk = ReturPenjualan_r::where('noretur', $validated['noretur'])
                ->count();
            if ($rincianAk == 0) $existingHeader->delete();
            DB::commit();

            return new JsonResponse([
                'success' => true,
                'data' => $rincian,
                'message' => 'Data rincian retur berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menghapus data rincian: ' . $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ], 410);
        }
    }
}
