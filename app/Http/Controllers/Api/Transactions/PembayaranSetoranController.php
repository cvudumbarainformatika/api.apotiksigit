<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting\ProfileToko;
use App\Models\Transactions\PembayaranSetoran;
use App\Models\Transactions\PembayaranSetoranRinci;
use App\Models\Transactions\PenjualanH;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class PembayaranSetoranController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];
        $raw = PembayaranSetoran::query();
        $raw->when(request('q'), function ($q) {
            $kode = User::where('nama', 'like', '%' . request('q') . '%')->pluck('kode');
            $q->where('notransaksi', 'like', '%' . request('q') . '%')
                ->orWhereIn('kode_user', $kode);
        })

            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw
            ->with('rinci')
            ->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
    public function getPenjualan()
    {
        $data = PenjualanH::select(
            'id',
            'nopenjualan',
            'tgl_penjualan',
            DB::raw('jumlah_bayar-kembali as nominal_cash'),
        )
            ->withSum('rinci as nominal_transaksi', 'subtotal')
            // total retur (BENAR)
            ->selectSub(function ($q) {
                $q->from('retur_penjualan_rs')
                    ->selectRaw('CAST(SUM((COALESCE(harga,0) * COALESCE(jumlah_k,0)) - COALESCE(diskon,0)) AS UNSIGNED)')
                    ->whereColumn('retur_penjualan_rs.nopenjualan', 'penjualan_h_s.nopenjualan');
            }, 'nominal_retur')
            ->whereNull('flag_setor')
            ->whereNotNull('flag')
            ->havingRaw('nominal_retur IS NULL OR nominal_cash > nominal_retur')
            ->get();
        return new JsonResponse([
            'data' => $data
        ]);
    }
    public function simpan(Request $request)
    {
        $notransaksi = $request->notransaksi ?? null;
        $user = Auth::user();
        $profile = ProfileToko::first();
        $kodeCabang = $profile->kode_toko;
        $validated = $request->validate([
            'nominal_setoran' => 'required|numeric',
            'penjualan' => 'required|array',

            'penjualan.*.nopenjualan' => 'required',
            'penjualan.*.nominal_transaksi' => 'required',
            'penjualan.*.nominal_cash' => 'required',
            'penjualan.*.nominal_retur' => 'nullable',

        ], [
            'nominal_setoran.required' => 'Nominal Setoran Harus di isi',
            'nominal_setoran.numeric' => 'Nominal Setoran Harus di Angka',

            'penjualan.nopenjualan.required' => 'Nomor Penjualan Harus ada',
            'penjualan.nominal_transaksi.required' => 'Nominal Transaksi Harus ada',
            'penjualan.nominal_cash.required' => 'Nominal Cash Harus ada',
        ]);
        try {
            DB::beginTransaction();
            $notIncludes = [];
            if (!$notransaksi) {
                $data = PembayaranSetoran::Create(

                    [
                        'kode_user' => $user->kode,
                        'kode_cabang' => $kodeCabang,
                        'nominal_setoran' => $validated['nominal_setoran']
                    ]
                );
                $lastId = $data->id;
                $notransaksi = FormatingHelper::genKodeDinLength(($lastId ?? 0), 5, 'STR');
                $data->update(['notransaksi' => $notransaksi]);
            } else {
                $nopenjualan = array_column($validated['penjualan'], 'nopenjualan');
                $notIncludes  = PembayaranSetoranRinci::where('notransaksi', $notransaksi)
                    ->whereNotIn('nopenjualan', $nopenjualan)
                    ->pluck('nopenjualan')
                    ->toArray();
                // header
                $data = PembayaranSetoran::where('notransaksi', $notransaksi)->first();
                if (!$data) throw new Exception('Pembayaran Setoran tidak tersimpan, tidak ada data dengan nomor transaksi ' . $notransaksi);
                if ($data->flag != null) throw new Exception('Tidak Boleh Di update. Data sudah dikunci!');
                $data->update(
                    [
                        'kode_user' => $user->kode,
                        'kode_cabang' => $kodeCabang,
                        'nominal_setoran' => $validated['nominal_setoran']
                    ]
                );
            }

            // rinci
            foreach ($validated['penjualan'] as $key) {
                $rinci = PembayaranSetoranRinci::updateOrCreate(
                    [
                        'pembayaran_setoran_id' => $data->id,
                        'notransaksi' => $notransaksi,
                        'nopenjualan' => $key['nopenjualan'],
                    ],
                    [
                        'nominal_transaksi' => $key['nominal_transaksi'],
                        'nominal_cash' => $key['nominal_cash'],
                        'nominal_retur' => $key['nominal_retur'] ?? 0,

                    ]
                );
                if (!$rinci) throw new Exception('rincian Setor untuk nomor penjualan ' . $key['nopenjualan'] . ' Gagal disimpan');
            }

            if (!empty($notIncludes)) {
                $rinci = PembayaranSetoranRinci::whereIn('nopenjualan', $notIncludes)->delete();
                $sisa = PembayaranSetoranRinci::where('notransaksi', $notransaksi)->count();
                if ($sisa == 0 && $rinci > 0) $data->delete();
            }

            DB::commit();
            $data->load('rinci');
            return new JsonResponse([
                'data' => $data,
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
    public function kunci(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:pembayaran_setorans,id'
        ], [
            'id.required' => 'Id Transaksi harus Ada',
            'id.exists' => 'Id Transaksi Tidak ditemukan. Pastikan Transaksi dengan Id tersebut ADA',
        ]);
        try {
            DB::beginTransaction();
            $data = PembayaranSetoran::find($validated['id']);
            if (!$data) throw new Exception('GAGAL KUNCI! Data Pembayaran Setoran tidak ditemukan');
            if ($data->flag === '1') throw new Exception('Pembayaran setoran sudah dikunci');

            $rinci = PembayaranSetoranRinci::where('pembayaran_setoran_id', $data->id)->pluck('nopenjualan')->toArray();
            if (empty($rinci)) throw new Exception('GAGAL KUNCI! Rincian Pembayaran Setoran tidak ditemukan');

            $penjualan = PenjualanH::whereIn('nopenjualan', $rinci)->get()->keyBy('nopenjualan');

            foreach ($rinci as $key) {
                if (!isset($penjualan[$key])) {
                    throw new Exception("Data penjualan dengan Nomor Transaksi $key tidak ditemukan");
                }
                $penjualan[$key]->update(['flag_setor' => 1]);
            }
            $data->update(['flag' => '1']);
            DB::commit();
            $data->load('rinci');
            return new JsonResponse([
                'message' => 'Pembayaran setoran berhasil dikunci',
                'data' => $data
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
    public function hapus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:pembayaran_setorans,id'
        ], [
            'id.required' => 'Id Transaksi harus Ada',
            'id.exists' => 'Id Transaksi Tidak ditemukan. Pastikan Transaksi dengan Id tersebut ADA',
        ]);
        try {
            DB::beginTransaction();
            $data = PembayaranSetoran::find($validated['id']);
            if (!$data) throw new Exception('GAGAL HAPUS! Data Pembayaran Setoran tidak ditemukan');
            if ($data->flag === '1') throw new Exception('Pembayaran setoran sudah dikunci, Tidak boleh dihapus');

            PembayaranSetoranRinci::where('pembayaran_setoran_id', $data->id)->delete();
            $data->delete();

            DB::commit();
            return new JsonResponse([
                'message' => 'Pembayaran setoran berhasil dihapus',
                'data' => $data
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
}
