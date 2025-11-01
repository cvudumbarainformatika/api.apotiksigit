<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Pendapatanlain_h;
use App\Models\Transactions\Pendapatanlain_r;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PendapatanlainController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = Pendapatanlain_h::query()
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('notransaksi', 'like', '%' . request('q') . '%')
                    ->orWhere('dari', 'like', '%' . request('q') . '%');
                });
            })
            ->with([
                'rincian',
            ])
            ->select('pendapatanlain_h.*')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function store(Request $request)
    {
        // return new JsonResponse($request->all());
        $validated = $request->validate([
            'notransaksi' => 'nullable',
            'tgl' => 'required',
            'dari' => 'required',
            'nilai' => 'required',
            'keterangan' => 'required',
        ], [
            'tgl.required' => 'Tanggal Transaksi Harus Di isi.',
            'dari.required' => 'Dari Harus Di isi.',
            'nilai.required' => 'Nilai Harus Di isi.',
            'keterangan.required' => 'Keterangan Harus Di isi.'
        ]);

        $user = Auth::user();

        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        try {
            DB::beginTransaction();
            if ($request->notransaksi === null || $request->notransaksi === '') {
                $notransaksi = date('YmdHis') . '-PLN';
            } else {
                $notransaksi = $request->notransaksi;
            }
            $data = Pendapatanlain_h::updateOrCreate([
                'notrans' => $notransaksi,
            ], [
                'tgl' => $validated['tgl'],
                'dari' => $validated['dari'],
                'user' => $user->kode,
            ]);
            if (!$data) {
                throw new \Exception('Data Pendapatan Lain Gagal Disimpan.');
            }
            $rinci = Pendapatanlain_r::updateOrCreate([
                'notrans' => $notransaksi,
                'keterangan' => $validated['keterangan'],
            ], [
                'nilai' => $validated['nilai'],
                'user' => $user->kode,

            ]);
            if (!$rinci) {
                throw new \Exception('Data Pendapatan Lain Gagal Disimpan.');
            }
            DB::commit();
           $datax =  Pendapatanlain_h::with(['rincian'])->where('notrans', $notransaksi)->first();
            return new JsonResponse([
                'message' => 'Data berhasil disimpan',
                'data' => $datax,
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

    public function lock(Request $request)
    {
        $validated = $request->validate([
            'notransaksi' => 'required',
        ], [
            'notransaksi.required' => 'Nomor Transaksi Harus Di isi.',
        ]);

        $existingHeader = Pendapatanlain_h::where('notrans', $validated['notransaksi'])->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Pendapatan Lain Tidak Ditemukan.'
            ], 410);
        }
        if ($existingHeader && $existingHeader->kunci == '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini sudah terkunci.'
            ], 410);
        }

        try {
            DB::beginTransaction();
                $existingHeader->update(['kunci' => '1']);
            DB::commit();
            $datax =  Pendapatanlain_h::with(['rincian'])->where('notrans', $validated['notransaksi'])->first();
            return new JsonResponse([
                'message' => 'Data berhasil disimpan',
                'data' => $datax,
            ], 201);
            return new JsonResponse([
                'success' => true,
                'message' => 'Data berhasil dikunci'
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

    public function delete(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required',
            'notransaksi' => 'required',
        ], [
            'id.required' => 'Nomor id Kosong.',
            'notransaksi.required' => 'Nomor Transaksi Harus Di isi.',
        ]);

        $existingRinci = Pendapatanlain_r::where('id', $validated['id'])->first();
        if (!$existingRinci) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Pendapatan Lain Tidak Ditemukan.'
            ], 410);
        }
        $cek = Pendapatanlain_h::where('notrans', $validated['notransaksi'])->where('kunci', '1')->count();
        if ($cek > 0) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini sudah terkunci.'
            ], 410);
        }

        try {
            DB::beginTransaction();
                $existingRinci->delete();
            DB::commit();
            $datax =  Pendapatanlain_h::with(['rincian'])->where('notrans', $validated['notransaksi'])->first();
            return new JsonResponse([
                'success' => true,
                'data' => $datax,
                'message' => 'Data berhasil dihapus'
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
}
