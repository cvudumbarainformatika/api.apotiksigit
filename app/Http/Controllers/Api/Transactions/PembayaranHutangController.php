<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\PembayaranHutang;
use App\Models\Transactions\PembayaranHutangRinci;
use App\Models\Transactions\Penerimaan_h;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembayaranHutangController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => Carbon::parse(request('from'))->startOfDay()->format('Y-m-d H:i:s') ?? Carbon::now()->startOfMonth()->startOfDay()->format('Y-m-d H:i:s'),
            'to' => Carbon::parse(request('to'))->endOfDay()->format('Y-m-d H:i:s') ?? Carbon::now()->endOfMonth()->endOfDay()->format('Y-m-d H:i:s')
        ];

        $raw = PembayaranHutang::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nopelunasan', 'like', '%' . request('q') . '%');
        })
            ->when($req['from'], function ($q) use ($req) {
                $q->whereBetween('tgl_pelunasan', [$req['from'], $req['to']]);
            })
            ->with([
                'rinci'
            ])
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
    public function getHutang()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => request('from') ?? Carbon::now()->startOfMonth()->format('Y-m-d'),
            'to' => request('to') ?? Carbon::now()->endOfMonth()->format('Y-m-d')
        ];
        $raw = Penerimaan_h::query()->select(
            'penerimaan_hs.nopenerimaan',
            'penerimaan_hs.noorder',
            'penerimaan_hs.nofaktur',
            'penerimaan_hs.kode_suplier',
            DB::raw('sum(r.jumlah_k*pajak_rupiah) as pajak'),
            DB::raw('sum(r.jumlah_k*diskon_rupiah) as diskon'),
            DB::raw('sum(r.jumlah_k*harga) as nominal'),
            DB::raw('sum(r.jumlah_k*harga_total) as nominal_total'),
            DB::raw('sum(r.subtotal) as total'),
        )
            ->leftJoin('penerimaan_rs as r', 'r.nopenerimaan', '=', 'penerimaan_hs.nopenerimaan')
            ->whereBetween('penerimaan_hs.tgl_penerimaan', [$req['from'], $req['to']])
            ->where('penerimaan_hs.hutang', 'HUTANG')
            ->whereNull('flag_hutang')
            ->where('penerimaan_hs.nopenerimaan', 'LIKE', '%' . request('q') . '%')
            ->with('rincian.barang:nama,kode,satuan_k,satuan_b,isi')
            ->groupBy('penerimaan_hs.nopenerimaan', 'penerimaan_hs.noorder', 'penerimaan_hs.nofaktur', 'penerimaan_hs.kode_suplier');
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        // return new JsonResponse([
        //     'resp' => $resp,
        //     'req' => $req,
        // ]);
        return new JsonResponse($resp);
    }
    public function getOneHutang()
    {

        $data['data'] = Penerimaan_h::query()->select(
            'penerimaan_hs.nopenerimaan',
            'penerimaan_hs.noorder',
            'penerimaan_hs.nofaktur',
            'penerimaan_hs.kode_suplier',
            DB::raw('sum(r.jumlah_k*pajak_rupiah) as pajak'),
            DB::raw('sum(r.jumlah_k*diskon_rupiah) as diskon'),
            DB::raw('sum(r.jumlah_k*harga) as nominal'),
            DB::raw('sum(r.jumlah_k*harga_total) as nominal_total'),
            DB::raw('sum(r.subtotal) as total'),
        )
            ->leftJoin('penerimaan_rs as r', 'r.nopenerimaan', '=', 'penerimaan_hs.nopenerimaan')
            ->where('penerimaan_hs.hutang', 'HUTANG')
            ->where('penerimaan_hs.nopenerimaan', request('q'))
            ->with([
                'rincian.barang:nama,kode,satuan_k,satuan_b,isi',
                'suplier:kode,nama'
            ])
            ->groupBy('penerimaan_hs.nopenerimaan', 'penerimaan_hs.noorder', 'penerimaan_hs.nofaktur', 'penerimaan_hs.kode_suplier')
            ->get();


        return new JsonResponse($data);
    }
    public function simpan(Request $request)
    {
        $nomorPen = $request->nopelunasan;
        $validated = $request->validate([

            'tgl_pelunasan' => 'nullable|date',
            'total_dibayar' => 'nullable',

            'noorder' => 'required',
            'nopenerimaan' => 'required',
            'nofaktur' => 'required',
            'kode_suplier' => 'required',
            'nominal' => 'required',
            'pajak' => 'required',
            'diskon' => 'required',
            'total' => 'required',
        ], [
            'noorder.required' => 'Nomor Order Harus Di isi.',
            'nopenerimaan.required' => 'Nomor Penerimaan Harus Di isi.',
            'nofaktur.required' => 'Nomor Faktur Harus Di isi.',
            'kode_suplier.required' => 'Supplier Harus Di isi.',
            'nominal.required' => 'Nominal Harus Di isi.',
            'pajak.required' => 'Pajak Harus Di isi.',
            'diskon.required' => 'Diskon Harus Di isi.',
            'total.required' => 'Total Harus Di isi.',
        ]);
        try {
            DB::beginTransaction();
            $user = Auth::user();
            if (!$nomorPen) {
                DB::select('call nomor_pembayaran_hutang(@nomor)');
                $nomor = DB::table('counter')->select('nomor_pembayaran_hutang')->first();
                $nopelunasan = FormatingHelper::genKodeBarang($nomor->nomor_pembayaran_hutang, 'TRXPEM');
            } else {
                $nopelunasan = $request->nopelunasan;
            }
            $data = PembayaranHutang::updateOrCreate([
                'nopelunasan' => $nopelunasan
            ], [
                'tgl_pelunasan' => $validated['tgl_pelunasan'] ?? Carbon::now()->format('Y-m-d H:i:s'),
                'total_dibayar' => $validated['total_dibayar'] ?? 0,
                'flag' => '',
                'kode_user' => $user->kode,
            ]);
            if (!$data) {
                throw new \Exception('Data Penjualan Gagal Disimpan.');
            }
            $rinci = PembayaranHutangRinci::updateOrCreate([
                'nopelunasan' => $nopelunasan,
                'noorder' => $validated['noorder'],
                'nopenerimaan' => $validated['nopenerimaan'],
                'nofaktur' => $validated['nofaktur'],
                'kode_suplier' => $validated['kode_suplier'],
            ], [
                'nominal' => $validated['nominal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'total' => $validated['total']
            ]);
            if (!$rinci) {
                throw new \Exception('Data Penjualan Gagal Disimpan.');
            }
            $tot = PembayaranHutangRinci::selectRaw('sum(total) as total')->where('nopelunasan',  $nopelunasan)->groupBy('nopelunasan')->first();
            if ($tot) {
                $data->update(['total_dibayar' => $tot->total]);
            }
            DB::commit();
            $data->load([
                'rinci'
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
    public function kunci(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required',
        ], [
            'id.required' => 'id transaksi Harus Di isi.',
        ]);
        try {
            DB::beginTransaction();
            $head = PembayaranHutang::find($validated['id']);
            if (!$head) throw new Exception('Transaksi Pembayaran hutang tidak ditemukan');
            if (!empty($head->flag) && $head->flag == '1') throw new Exception('Transaksi Pembayaran hutang Sudah dikunci');

            $rinc = PembayaranHutangRinci::where('nopelunasan',  $head->nopelunasan)->get();
            $tot = $rinc->sum('total');
            if ($rinc->isNotEmpty()) {
                Penerimaan_h::whereIn('nopenerimaan', $rinc->pluck('nopenerimaan'))
                    ->update(['flag_hutang' => '1']);
            }
            $head->update([
                'total_dibayar' => $tot ?? 0,
                'flag' => '1',
            ]);
            $head->load([
                'rinci'
            ]);
            DB::commit();
            return new JsonResponse([
                'message' => 'Data berhasil dikunci',
                'data' => $head
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal Mengunci data: ' . $e->getMessage(),
                'line' => $e->getLine(),

            ], 410);
        }
    }
    public function hapus(Request $request)
    {
        $validated = $request->validate([
            'noorder' => 'required',
            'nopenerimaan' => 'required',
            'nopelunasan' => 'required',
        ], [
            'noorder.required' => 'Nomor Order Harus Di isi.',
            'nopenerimaan.required' => 'Nomor Penerimaan Harus Di isi.',
            'nopelunasan.required' => 'Nomor Pelunasan Harus Di isi.',
        ]);

        try {
            DB::beginTransaction();
            $head = PembayaranHutang::where('nopelunasan', $validated['nopelunasan'])->first();
            if (!$head) throw new Exception('Transaksi Pembayaran tidak ditemukan');
            if ($head->flag != '') throw new Exception('Transaksi Pembayaran sudah dikunci');
            $data = PembayaranHutangRinci::where('nopelunasan', $validated['nopelunasan'])->where('noorder', $validated['noorder'])->where('nopenerimaan', $validated['nopenerimaan'])->first();
            if (!$data) throw new Exception('Transaksi Rincian Pembayaran tidak ditemukan');
            $data->delete();
            $jum = PembayaranHutangRinci::where('nopelunasan', $validated['nopelunasan'])->count();
            if ($jum <= 0) $head->delete();
            DB::commit();
            return new JsonResponse([
                'message' => 'Data berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'line' => $e->getLine(),

            ], 410);
        }
    }
    public function bukaKunci(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:pembayaran_hutangs,id',
        ], [
            'id.required' => 'ID transaksi harus diisi.',
            'id.exists' => 'Transaksi pembayaran hutang tidak ditemukan.',
        ]);

        try {
            DB::beginTransaction();

            $head = PembayaranHutang::find($validated['id']);
            if (!$head) throw new Exception('Transaksi Pembayaran hutang tidak ditemukan');

            if (empty($head->flag) || $head->flag != '1') {
                throw new Exception('Transaksi belum dikunci, tidak bisa dibuka');
            }

            $rinc = PembayaranHutangRinci::where('nopelunasan', $head->nopelunasan)->get();

            if ($rinc->isNotEmpty()) {
                // reset flag_hutang semua penerimaan terkait
                Penerimaan_h::whereIn('nopenerimaan', $rinc->pluck('nopenerimaan'))
                    ->update(['flag_hutang' => null]);
            }

            $head->update([
                'flag' => '', // buka kunci
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Transaksi berhasil dibuka kuncinya',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuka kunci: ' . $e->getMessage(),
                'line' => $e->getLine(),
            ], 410);
        }
    }
}
