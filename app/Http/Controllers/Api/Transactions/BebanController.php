<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Beban_h;
use App\Models\Transactions\Beban_r;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BebanController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = Beban_h::query()
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('notransaksi', 'like', '%' . request('q') . '%');
                });
            })
            ->with([
                'rincian' => function ($query) {
                    $query->with(['mbeban']);
                },
            ])
            ->select('beban_hs.*')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function simpan(Request $request)
    {

        try {
            DB::beginTransaction();
            $user = Auth::user();
            if($request->notransaksi != null || $request->notransaksi != ''){
                $notransaksi = $request->notransaksi;
            }else{
                $notransaksi = date('YmdHis').'-BB';
            }
            $bebanHeder = Beban_h::updateOrCreate(
                [
                    'notransaksi' => $notransaksi,
                ],
                [
                    'keterangan' => $request->keterangan,
                    'kode_user' => $user->kode,
                    'flag' => '',
                ]
            );

            $cek = Beban_h::where('notransaksi', $request->notransaksi)->where('flag', '1')->count();
            if ($cek > 0) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Data ini sudah terkunci.'
                ], 410);
            }
            $bebanRinci = Beban_r::create(
                [
                    'notransaksi' => $notransaksi,
                    'kode_beban' => $request->kode_beban,
                    'subtotal' => $request->subtotal,
                    'kode_user' => $user->kode
                ]
            );

            DB::commit();
            $bebanHeder->load([
                'rincian' => function ($query) {
                    $query->with(['mbeban']);
                },
            ]);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'header' => $bebanHeder,
                ],
                'message' => 'Data berhasil disimpan'
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
        $cek = Beban_h::where('notransaksi', $request->notransaksi)->where('flag', '1')->count();
        if ($cek > 0) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini sudah terkunci.'
            ], 410);
        }

        try {
            DB::beginTransaction();
            // Hapus order records
            Beban_r::where('id', $request->id)->delete();

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 410);
        }
    }

    public function lock_beban(Request $request)
    {

        try {
            DB::beginTransaction();
                $update = Beban_h::where('notransaksi', $request->notransaksi)->first();
                $update->update(['flag' => '1']);

            DB::commit();
            $update->load([
                'rincian' => function ($query) {
                    $query->with(['mbeban']);
                },
            ]);
            return new JsonResponse([
                'success' => true,
                'data' => $update,
                'message' => 'Data Penerimaan berhasil Dikunci'
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
