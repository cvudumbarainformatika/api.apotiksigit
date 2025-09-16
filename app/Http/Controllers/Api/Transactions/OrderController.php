<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\OrderHeader;
use App\Models\Transactions\OrderRecord;
use App\Models\Transactions\Penerimaan_h;
use Date;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Fix Controller 
     * 
     * Flagging : 
     * null = draft
     * 1 = kunci
     * 2 = kunci double
     */

    // get list of orders (header & records)
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'from' => request('from'),
            'to' => request('to'),
            'flag' => request('flag'),
            'per_page' => request('per_page', 10),
        ];

        $query = OrderHeader::query()
            ->select('order_headers.*')
            ->leftJoin('suppliers', 'order_headers.kode_supplier', '=', 'suppliers.kode')
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('nomor_order', 'like', '%' . request('q') . '%')
                        ->orWhere('kode_user', 'like', '%' . request('q') . '%')
                        ->orWhere('suppliers.nama', 'LIKE', '%' . request('q') . '%');
                });
            })
            ->when(isset($req['flag']), function ($q) use ($req) {
                if ($req['flag'] === 'null' || $req['flag'] === null || $req['flag'] === '') {
                    $q->whereNull('order_headers.flag');
                } else if (is_numeric($req['flag'])) {
                    $q->where('order_headers.flag', (int) $req['flag'])
                        ->whereNull('status_penerimaan');
                }
            })
            ->when($req['from'] || $req['to'], function ($q) use ($req) {
                if ($req['from']) {
                    $q->whereDate('tgl_order', '>=', $req['from']);
                }
                if ($req['to']) {
                    $q->whereDate('tgl_order', '<=', $req['to']);
                }
            })
            ->with([
                'orderRecords.master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'supplier',
                'penerimaan.rincian'
            ])
            ->orderBy('order_headers.' . $req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    // Store a new order (header & records)
    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'nomor_order' => 'nullable',
            'tgl_order' => 'nullable',
            'kode_supplier' => 'required',
            'kode_barang' => 'required',
            'jumlah_pesan' => 'required',
            'satuan_k' => 'nullable',
            'satuan_b' => 'nullable',
            'isi' => 'required',
        ], [
            'kode_supplier.required' => 'Kode Supplier Harus Di isi.',
            'jumlah_pesan.required' => 'Jumlah Pesanan Harus Di isi.',
            'items.required' => 'Minimal satu barang harus dipilih.',
            'kode_barang.required' => 'Kode Barang Harus Di isi.',
            'isi.required' => 'Isi per Satuan Besar Barang Harus Di isi.',
        ]);
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }
        if (!$validated['nomor_order']) {
            DB::select('call nomor_order(@nomor)');
            $nomor = DB::table('counter')->select('nomor_order')->first();
            $nomor_order = FormatingHelper::genKodeBarang($nomor->nomor_order, 'TRX');
        } else {
            $nomor_order = $request->nomor_order;

            // Cek apakah order sudah ada dan sudah terkunci
            $existingHeader = OrderHeader::where('nomor_order', $nomor_order)->first();
            if ($existingHeader && $existingHeader->flag == '1') {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Data Order Sudah Terkunci'
                ], 403);
            }
        }

        $checkPenerimaan = Penerimaan_h::where('noorder', $validated['nomor_order'])->first();
        // jika penerimaan ditemukan maka return false
        if ($checkPenerimaan) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Order ini Sudah Masuk Ke penerimaan.'
            ], 410);
        }

        try {
            DB::beginTransaction();
            $orderHeader = OrderHeader::updateOrCreate(
                [
                    'nomor_order' => $nomor_order,
                ],
                [
                    'kode_user' => $user->kode,
                    'kode_supplier' => $validated['kode_supplier'],
                    'tgl_order' => $validated['tgl_order'] ?? now(),
                ]
            );
            if (!$orderHeader) {
                throw new \Exception('Transaksi Gagal Disimpan.');
            }

            $record = OrderRecord::updateOrCreate(
                [
                    'nomor_order' => $nomor_order,
                    'kode_barang' => $validated['kode_barang'],
                ],
                [
                    'kode_user' => $user->kode,
                    'jumlah_pesan' => $validated['jumlah_pesan'],
                    'satuan_b' => $validated['satuan_b'] ?? null,
                    'satuan_k' => $validated['satuan_k'] ?? null,
                    'isi' => $validated['isi'] ?? 1,
                ]
            );

            if (!$record) {
                throw new \Exception('Gagal menyimpan Rincian .');
            }

            DB::commit();
            $orderHeader = OrderHeader::with([
                'orderRecords.master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'supplier',
            ])->find($orderHeader->id);

            return new JsonResponse([
                'success' => true,
                'data' => $orderHeader,
                'message' => 'Data Orders berhasil disimpan'
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

    // Lock Order Hanya untuk Data Draft
    public function lock_order(Request $request)
    {
        $validated = $request->validate([
            'nomor_order' => 'required',
        ], [
            'nomor_order.required' => 'Nomor Order Harus Di isi.',
        ]);
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }
        $existingHeader = OrderHeader::where('nomor_order', $validated['nomor_order'])->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Order Tidak Ditemukan.'
            ], 410);
        }
        if ($existingHeader && $existingHeader->flag == '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini sudah terkunci.'
            ], 410);
        }

        // Jika flag awalnya null = boleh rubah ke flag 1 / lock table
        if ($existingHeader && $existingHeader->flag != null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini bukan data Draft'
            ], 410);
        }

        try {
            DB::beginTransaction();
            $orderHeader = OrderHeader::where(
                [
                    'nomor_order' => $validated['nomor_order'],
                ]
            )->update(
                [
                    'flag' => '1', // Lock Table
                ]
            );
            if (!$orderHeader) {
                throw new \Exception('Transaksi Gagal Disimpan.');
            }

            $record = OrderRecord::where(
                [
                    'nomor_order' => $validated['nomor_order'],
                ],
            )->update(
                [
                    'flag' => '1'
                ]
            );

            if (!$record) {
                throw new \Exception('Gagal menyimpan Rincian.');
            }

            DB::commit();
            $orderHeader = OrderHeader::with([
                'orderRecords.master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'supplier',
            ])->find($existingHeader->id);

            return new JsonResponse([
                'success' => true,
                'data' => $orderHeader,
                'message' => 'Data Orders berhasil Dikunci'
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

    // Open Lock Order hanya boleh sebelum penerimaan
    public function open_lock_order(Request $request)
    {
        $validated = $request->validate([
            'nomor_order' => 'required',
        ], [
            'nomor_order.required' => 'Nomor Order Harus Di isi.',
        ]);
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }
        $existingHeader = OrderHeader::where('nomor_order', $validated['nomor_order'])->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Order Tidak Ditemukan.'
            ], 410);
        }
        if ($existingHeader && $existingHeader->flag == null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini belum terkunci.'
            ], 410);
        }
        // Jika flag kunci table 1 = Tidak boleh rubah ke flag null / lock table jika sudah masuk ke penerimaan
        if ($existingHeader && $existingHeader->flag == '1') {
            $checkPenerimaan = Penerimaan_h::where('noorder', $validated['nomor_order'])->first();

            // jika penerimaan ditemukan maka return false
            if ($checkPenerimaan) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Data Order ini Sudah Masuk Ke penerimaan.'
                ], 410);
            }
        }

        if ($existingHeader && $existingHeader->flag != 1) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini bukan data Order Terkunci.'
            ], 410);
        }

        try {
            DB::beginTransaction();
            $orderHeader = OrderHeader::where(
                [
                    'nomor_order' => $validated['nomor_order'],
                ]
            )->update(
                [
                    'flag' => null, // Lock Table
                ]
            );
            if (!$orderHeader) {
                throw new \Exception('Transaksi Gagal Disimpan.');
            }

            $record = OrderRecord::where(
                [
                    'nomor_order' => $validated['nomor_order'],
                ]
            )->update(
                [
                    'flag' => null
                ]
            );

            if (!$record) {
                throw new \Exception('Gagal menyimpan Rincian.');
            }

            DB::commit();
            $orderHeader = OrderHeader::with([
                'orderRecords.master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'supplier',
            ])->find($existingHeader->id);

            return new JsonResponse([
                'success' => true,
                'data' => $orderHeader,
                'message' => 'Kunci Data Orders Berhasil Dibuka'
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

    // Delete an order (header & records) kurang cek status jika status > 1 tidak boleh hapus
    public function hapus(Request $request)
    {
        $nomor_order = $request->nomor_order;

        if (!$nomor_order) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Nomor order tidak ditemukan'
            ], 400);
        }

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        $existingHeader = OrderHeader::where('nomor_order', $nomor_order)->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'succes' => false,
                'message' => 'Data Order Tidak Ditemukan.'
            ], 410);
        }

        // Tidak boleh hapus jika sudah masuk ke penerimaan
        $checkPenerimaan = Penerimaan_h::where('noorder', $nomor_order)->first();
        // jika penerimaan ditemukan maka return false
        if ($checkPenerimaan) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Order ini Sudah Masuk Ke penerimaan.'
            ], 410);
        }

        if ($existingHeader && $existingHeader->flag == '1') {
            return new JsonResponse([
                'succes' => false,
                'message' => 'Data Order ini Tidak Dapat dirubah.'
            ], 410);
        }

        try {
            DB::beginTransaction();
            // Hapus order records
            OrderRecord::where('nomor_order', $nomor_order)->delete();

            // Hapus order header
            $header = OrderHeader::where('nomor_order', $nomor_order)->first();
            if ($header) {
                $header->delete();
            }

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'message' => 'Data order berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 410);
        }
    }

    // Delete an order record
    public function record_hapus(Request $request)
    {
        $nomor_order = $request->nomor_order;
        $kode_barang = $request->kode_barang;

        if (!$nomor_order || !$kode_barang) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Nomor order dan kode barang harus diisi'
            ], 410);
        }

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        // Tidak boleh hapus jika sudah masuk ke penerimaan
        $checkPenerimaan = Penerimaan_h::where('noorder', $nomor_order)->first();
        // jika penerimaan ditemukan maka return false
        if ($checkPenerimaan) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Order ini Sudah Masuk Ke penerimaan.'
            ], 410);
        }

        $existingHeader = OrderHeader::where('nomor_order', $nomor_order)->first();
        if ($existingHeader && $existingHeader->flag == '1') {
            return new JsonResponse([
                'succes' => false,
                'message' => 'Data Order ini Tidak Dapat dirubah.'
            ], 410);
        }

        try {
            DB::beginTransaction();

            $record = OrderRecord::where('nomor_order', $nomor_order)
                ->where('kode_barang', $kode_barang)
                ->first();

            if (!$record) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Data record tidak ditemukan'
                ], 410);
            }

            $record->delete();

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'data' => $record,
                'message' => 'Data record berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menghapus data record: ' . $e->getMessage()
            ], 410);
        }
    }
}
