<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use App\Models\Master\Cabang;
use App\Models\Setting\ProfileToko;
use App\Models\Transactions\MutasiHeader;
use App\Models\Transactions\MutasiRequest;
use App\Models\Transactions\Stok;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MutasiController extends Controller
{
    public function getCabang()
    {
        // $profile = ProfileToko::first();
        $data = Cabang::select('kodecabang', 'namacabang')
            // ->where('kodecabang', '!=', $profile->kode_toko)
            ->get()->toArray();


        return new JsonResponse([
            'data' => $data
        ]);
    }
    public function getBarang()
    {
        $req = [
            'order_by' => request('order_by') ?? 'nama',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'depo' => request('depo') ?? null,
        ];
        $data = Barang::when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })->with([
            'stok' => function ($q) use ($req) {
                $q->where('kode_depo', $req['depo']);
            }
        ])->orderBy($req['order_by'], $req['sort'])
            ->limit($req['per_page'])
            ->get();
        return new JsonResponse([
            'data' => $data
        ]);
    }
    public function index()
    {
        // return request()->all();
        // return !!(request('tujuan'));
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => request('from') ?? Carbon::now()->format('Y-m-d'),
            'to' => request('to') ?? Carbon::now()->format('Y-m-d'),
        ];
        $profile = ProfileToko::first();
        $raw = MutasiHeader::query();
        $raw->when(request('q'), function ($q) {
            $q->where('kode_mutasi', 'like', '%' . request('q') . '%');
        })
            ->when($req['from'], function ($q) use ($req) {
                $q->whereBetween('tgl_permintaan', [$req['from'] . ' 00:00:00', $req['to'] . ' 23:59:59']);
            })
            ->when(
                request('status') == null,
                function ($q) {
                    $q->whereNull('status');
                },
                function ($r) {
                    if (request('status') != 'all') $r->where('status', request('status'));
                }
            )
            ->when(request('dari'), function ($q) {
                $q->where('dari', request('dari'));
            })
            ->when(request('tujuan'), function ($q) {
                $q->where('tujuan', request('tujuan'));
            })
            ->with([
                'rinci' => function ($q) use ($profile) {
                    $q->with([
                        'master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                        'stok' => function ($r) use ($profile) {
                            $r->where('kode_depo', $profile->kode_toko);
                        },
                        'stokGudang' => function ($r) {
                            $r->where('kode_depo', 'APS0000');
                        },
                    ]);
                },
                'dari',
                'tujuan'
            ])
            ->orderBy($req['order_by'], $req['sort']);
        if (request()->has('tujuan')) {

            if (request('tujuan') == null || request('tujuan') == 'gudang') $raw->where('tujuan', 'APS0000');
            else $raw->where('tujuan', request('tujuan'));
        }


        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
    public function simpan(Request $request)
    {
        $kode = $request->kode_mutasi;
        $validated = $request->validate([

            'tgl_permintaan' => 'nullable',
            'kode_barang' => 'required',
            'tujuan' => 'required',
            'jumlah_k' => 'required',
            'harga_beli' => 'required',
            'satuan_k' => 'nullable',
            'pengirim' => 'nullable',
            'dari' => 'nullable',
        ], [
            'kode_barang.required' => 'Kode Barang Harus Di isi.',
            'jumlah_k.required' => 'Jumalah Barang Harus Di isi.',
            'tujuan.required' => 'Tujuan Permintaan harus di isi.',
            'harga_beli.required' => 'Harga Beli Harus Di isi.',
        ]);
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $profile = ProfileToko::first();
            if (!$kode) {
                DB::select('call kode_mutasi(@nomor)');
                $nomor = DB::table('counter')->select('kode_mutasi')->first();
                $kode_mutasi = FormatingHelper::genKodeBarang($nomor->kode_mutasi, 'TRX');
            } else {
                $kode_mutasi = $request->kode_mutasi;
            }
            $tgl_permintaan = $validated['tgl_permintaan'] . date(' H:i:s') ?? Carbon::now()->format('Y-m-d H:i:s');
            $pengirim = $validated['pengirim']  ?? $user->kode;
            $dari = $validated['dari']  ?? $profile->kode_toko;
            $data = MutasiHeader::updateOrCreate([
                'kode_mutasi' => $kode_mutasi
            ], [
                'tgl_permintaan' => $tgl_permintaan,
                'pengirim' => $pengirim,
                'dari' => $dari,
                'tujuan' => $validated['tujuan'],
            ]);
            $data->rinci()->updateOrCreate([
                'kode_mutasi' => $kode_mutasi,
                'kode_barang' => $validated['kode_barang'],
            ], [
                'jumlah' => $validated['jumlah_k'],
                'harga_beli' => $validated['harga_beli'],
                'satuan_k' => $validated['satuan_k'],
            ]);
            DB::commit();
            $data->load([
                'rinci' => function ($q) {
                    $profile = ProfileToko::first();
                    $q->with([
                        'master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                        'stok' => function ($r) use ($profile) {
                            $r->where('kode_depo', $profile->kode_toko);
                        },
                        'stokGudang' => function ($r) use ($profile) {
                            $r->where('kode_depo', 'APS0000');
                        },
                    ]);
                }
            ]);
            return new JsonResponse([
                'message' => 'Data berhasil disimpan',
                'data' => $data,
                // 'rinci' => $rinci,
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
    public function hapus(Request $request)
    {
        // return $request->all();
        $validated = $request->validate([
            'kode_barang' => 'required',
            'kode_mutasi' => 'required',
        ], [
            'kode_barang.required' => 'Tidak Ada Rincian untuk dihapus',
            'kode_mutasi.required' => 'Nomor Transaksi Harus di isi',
        ]);
        try {
            DB::beginTransaction();
            $msg = 'Rincian Obat sudah dihapus';
            $rinci = MutasiRequest::where('kode_barang', $validated['kode_barang'])->where('kode_mutasi', $validated['kode_mutasi'])->first();
            if (!$rinci) throw new Exception('Data Obat tidak ditemukan');
            $header = MutasiHeader::where('kode_mutasi', $validated['kode_mutasi'])->first();
            if (!$header) throw new Exception('Data Header Mutasi tidak ditemukan, transaksi tidak bisa dilanjutkan');
            if ($header->status !== null) throw new Exception('Data sudah terkunci, tidak boleh dihapus');
            // hapus rincian
            $rinci->delete();
            // hitung sisa rincian
            $sisaRinci = MutasiRequest::where('kode_mutasi', $validated['kode_mutasi'])->count();
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
    public function kirim(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required',
        ], [

            'id.required' => 'Id Header Mutasi harus di isi',
        ]);
        try {
            DB::beginTransaction();
            $mutasi = MutasiHeader::find($validated['id']);
            if (!$mutasi) throw new Exception('Data Transaksi Mutasi tidak ditemukan');
            if ($mutasi->status == '1') throw new Exception('Transaksi sudah dikirim');

            $cabangTujuan = Cabang::where('kodecabang', $mutasi->tujuan)->first();
            if (!$cabangTujuan) throw new Exception('Tujuan Mutasi tidak ditemukan, mohon cek tujuan mutasi');


            $mutasi->update(['status' => '1']);
            $mutasi->load([
                'rinci' => function ($q) {
                    $profile = ProfileToko::first();
                    $q->with([
                        'master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                        'stok' => function ($r) use ($profile) {
                            $r->where('kode_depo', $profile->kode_toko);
                        },
                        'stokGudang' => function ($r) use ($profile) {
                            $r->where('kode_depo', 'APS0000');
                        },
                    ]);
                }
            ]);
            $data = (object)[
                'mutasi' => $mutasi,
                'transaction' => 'permintaan'
            ];
            $url = $cabangTujuan->url . 'v1/transactions/curl-mutasi/terima-curl';
            $kirim = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($url, $data);
            $resp = json_decode($kirim, true);
            $feed = $kirim->json('feedback');
            $status = $kirim->status();
            $code = $feed['code'];

            if ((int)$status != 200 && (int)$code != 200) throw new Exception(json_encode($resp));
            DB::commit();

            return new JsonResponse([
                'status' => $status,
                'code' => $code,
                'feed' => $feed,
                'data' => $mutasi,
                'message' => 'Permintaan Mutasi Sudah dikirim',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $data = json_decode($e->getMessage(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return response()->json($data, $data['code'] ?? 410);
            }
            return response()->json([
                'message' =>  $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
    public function simpanDistribusi(Request $request)
    {
        $validated = $request->validate([
            'kode_mutasi' => 'required',
            'kode_barang' => 'required',
            'distribusi' => 'required',
            'harga_beli' => 'required',
            'satuan_k' => 'required',
        ], [
            'kode_mutasi.required' => 'Nomor Transaksi Harus di isi',
            'kode_barang.required' => 'Kode Barang Harus di isi',
            'distribusi.required' => 'Jumlah yang akan di distribusikan Harus di isi',
            'harga_beli.required' => 'Harga Beli Harus di isi',
            'satuan_k.required' => 'Satuan Harus di isi',
        ]);
        try {
            DB::beginTransaction();
            $data = MutasiHeader::where('kode_mutasi', $validated['kode_mutasi'])->first();
            if (!$data) throw new Exception('Data mutasi tidak ditemukan, transaksi tidak dapat dilanjutkan');
            $rinci = MutasiRequest::where('kode_mutasi', $validated['kode_mutasi'])->where('kode_barang', $validated['kode_barang'])->first();
            if (!$rinci) throw new Exception('Data Barang tidak ditemukan, transaksi tidak dapat dilanjutkan');
            // cek stok -> yang penting ada... nilainya boleh minus
            $stok = Stok::where('kode_barang', $validated['kode_barang'])->where('kode_depo', $data->tujuan)->first();
            if (!$stok) throw new Exception('Tidak ada data stok untuk obat ini');
            $rinci->update([
                'harga_beli' => $validated['harga_beli'],
                'distribusi' => $validated['distribusi'],
                'satuan_k' => $validated['satuan_k'],
            ]);
            DB::commit();
            $data->load([
                'rinci' => function ($q) {
                    $profile = ProfileToko::first();
                    $q->with([
                        'master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                        'stok' => function ($r) use ($profile) {
                            $r->where('kode_depo', $profile->kode_toko);
                        },
                        'stokGudang' => function ($r) use ($profile) {
                            $r->where('kode_depo', 'APS0000');
                        },
                    ]);
                }
            ]);
            return new JsonResponse([
                'message' => 'Data sudah disimpan',
                'data' => $data,
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
    public function kirimDistribusi(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required',
        ], [

            'id.required' => 'Id Header Mutasi harus di isi',
        ]);
        try {
            DB::beginTransaction();
            $mutasi = MutasiHeader::find($validated['id']);
            if (!$mutasi) throw new Exception('Data Transaksi Mutasi tidak ditemukan');
            if ($mutasi->status == '2') throw new Exception('Data Transaksi Mutasi Sudah di disatribusikan');

            $cabangTujuan = Cabang::where('kodecabang', $mutasi->tujuan)->first();
            if (!$cabangTujuan) throw new Exception('Tujuan Mutasi tidak ditemukan, mohon cek tujuan mutasi');


            // ambil rincian
            $rinci = MutasiRequest::where('mutasi_header_id', $mutasi->id)->get();
            $kode = $rinci->pluck('kode_barang');
            $stok = Stok::lockForUpdate()->whereIn('kode_barang', $kode)->where('kode_depo', $mutasi->tujuan)->get();
            // kurangi stok
            foreach ($rinci as $key) {
                $stk = $stok->firstWhere('kode_barang', $key['kode_barang']);
                if (!$stk) throw new Exception('Data Stok tidak ditemukan');
                $ada = (float) $stk->jumlah_k;
                $dist = (float) $key['distribusi'];
                $sisa = $ada - $dist;
                $stk->update(['jumlah_k' => $sisa]);
            }

            $mutasi->update([
                'status' => '2',
                'tgl_distribusi' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
            $data = (object)[
                'mutasi' => $mutasi,
                'transaction' => 'distribusi'
            ];
            $url = $cabangTujuan->url . 'v1/transactions/curl-mutasi/terima-curl';
            $kirim = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($url, $data);
            $resp = json_decode($kirim, true);
            $feed = $kirim->json('feedback');
            $status = $kirim->status();
            $code = $feed['code'];

            if ((int)$status != 200 || (int)$code != 200) throw new Exception(json_encode($resp));
            DB::commit();
            $mutasi->load([
                'rinci' => function ($q) {
                    $profile = ProfileToko::first();
                    $q->with([
                        'master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                        'stok' => function ($r) use ($profile) {
                            $r->where('kode_depo', $profile->kode_toko);
                        },
                        'stokGudang' => function ($r) use ($profile) {
                            $r->where('kode_depo', 'APS0000');
                        },
                    ]);
                }
            ]);
            return new JsonResponse([
                'if' => (int)$status != 200 || (int)$code != 200,
                'feed' => $feed,
                'status' => $status,
                'code' => $code,
                'message' => 'Data Distribusi Sudah dikirim',
                'data' => $mutasi,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $data = json_decode($e->getMessage(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return response()->json($data, $data['code'] ?? 410);
            }
            return response()->json([
                'message' =>  $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
    public function terima(Request $request)
    {
        $validated = $request->validate([
            'kode_mutasi' => 'required',
            'penerima' => 'nullable',
        ], [
            'kode_mutasi.required' => 'Nomor Transaksi Harus di isi',
        ]);
        try {
            DB::beginTransaction();
            $mutasi = MutasiHeader::where('kode_mutasi', $validated['kode_mutasi'])->first();
            $profile = ProfileToko::first();
            if (!$mutasi) throw new Exception('Data Transaksi Mutasi tidak ditemukan');
            if ($mutasi->status == '3') throw new Exception('Data Transaksi Mutasi sudah Diterima');
            // ambil rincian
            $rinci = MutasiRequest::where('kode_mutasi', $validated['kode_mutasi'])->get();
            $kode = $rinci->pluck('kode_barang');
            $stok = Stok::lockForUpdate()->whereIn('kode_barang', $kode)->where('kode_depo', $mutasi->dari)->get();
            // kurangi stok
            foreach ($rinci as $key) {
                $stk = $stok->firstWhere('kode_barang', $key['kode_barang']);
                $dist = (float) $key['distribusi'];
                if (!$stk) {
                    Stok::create([
                        'kode_depo' => $profile->kode_toko,
                        'kode_barang' => $key['kode_barang'],
                        'satuan_k' => $key['satuan_k'],
                        'jumlah_k' => $dist,
                    ]);
                } else {
                    $ada = (float) $stk->jumlah_k;
                    $sisa = $ada + $dist;
                    $stk->update(['jumlah_k' => $sisa]);
                }
            }
            $user = Auth::user();
            $penerima = $validated['penerima']  ?? $user->kode;
            $mutasi->update([
                'status' => '3',
                'penerima' => $penerima,
                'tgl_terima' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
            DB::commit();
            $mutasi->load([
                'rinci' => function ($q) use ($profile) {
                    $q->with([
                        'master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                        'stok' => function ($r) use ($profile) {
                            $r->where('kode_depo', $profile->kode_toko);
                        },
                        'stokGudang' => function ($r) {
                            $r->where('kode_depo', 'APS0000');
                        },
                    ]);
                }
            ]);
            return new JsonResponse([
                'message' => 'Mutasi Sudah diterima',
                'data' => $mutasi,
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

    public function terimaCurl(Request $request)
    {

        $message = 'Berhasil menyimpan data';
        $code = 200;
        if ($request->transaction == 'permintaan') $trx = self::curlKirimPermintaan($request->mutasi);
        if ($request->transaction == 'distribusi') $trx = self::curlKirimDistribusi($request->mutasi);
        $code = $trx['code'] ?? 410;
        if ($code == 410) $message = $trx['message'];
        $feedback = [
            'message' => $message,
            'code' => $code,
            'trx' => $trx ?? null,
            // 'rinci' => $request->mutasi['rinci'],
            // 'request' => $request->all(),
        ];
        return new JsonResponse([
            'feedback' => $feedback
        ]);
    }
    public static function curlKirimPermintaan($req)
    {
        // $mutasi = MutasiHeader::where('kode_mutasi', $req['kode_mutasi'])->where('dari', $req['dari'])->where('tujuan', $req['tujuan'])->first();
        try {
            DB::beginTransaction();

            $mutasi = MutasiHeader::updateOrCreate([
                'kode_mutasi' => $req['kode_mutasi'],
                'dari' => $req['dari'],
                'tujuan' => $req['tujuan']
            ], [
                'pengirim' => $req['pengirim'],
                'penerima' => $req['penerima'],
                'tgl_permintaan' => $req['tgl_permintaan'],
                'tgl_distribusi' => $req['tgl_distribusi'],
                'tgl_terima' => $req['tgl_terima'],
                'status' => $req['status'],

            ]);
            foreach ($req['rinci'] as $rinci) {
                // return $rinci['kode_mutasi'];
                $mutasi->rinci()->updateOrCreate([
                    'mutasi_header_id' => $mutasi->id,
                    'kode_mutasi' => $rinci['kode_mutasi'],
                    'kode_barang' => $rinci['kode_barang'],
                ], [
                    'jumlah' => $rinci['jumlah'],
                    'harga_beli' => $rinci['harga_beli'],
                    'satuan_k' => $rinci['satuan_k'],
                ]);
            }
            $mutasi->load('rinci');
            DB::commit();
            return [
                // 'rinci' => $req['rinci'],
                'mutasi' => $mutasi,
                // 'requset' => $req,
                'code' => 200

            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'code' => 410,
                'message' =>  $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ];
        }
    }
    public static function curlKirimDistribusi($req)
    {
        try {
            DB::beginTransaction();
            // $mutasi = MutasiHeader::where('kode_mutasi', $req['kode_mutasi'])->where('dari', $req['dari'])->where('tujuan', $req['tujuan'])->first();
            $mutasi = MutasiHeader::updateOrCreate([
                'kode_mutasi' => $req['kode_mutasi'],
                'dari' => $req['dari'],
                'tujuan' => $req['tujuan']
            ], [
                'pengirim' => $req['pengirim'],
                'penerima' => $req['penerima'],
                'tgl_permintaan' => $req['tgl_permintaan'],
                'tgl_distribusi' => $req['tgl_distribusi'],
                'tgl_terima' => $req['tgl_terima'],
                'status' => $req['status'],

            ]);
            foreach ($req['rinci'] as $rinci) {
                // return $rinci['kode_mutasi'];
                $mutasi->rinci()->updateOrCreate([
                    'mutasi_header_id' => $mutasi->id,
                    'kode_mutasi' => $rinci['kode_mutasi'],
                    'kode_barang' => $rinci['kode_barang'],
                ], [
                    'distribusi' => $rinci['distribusi'],
                    'harga_beli' => $rinci['harga_beli'],
                    'satuan_k' => $rinci['satuan_k'],
                ]);
            }

            $mutasi->load('rinci');
            DB::commit();
            return [
                // 'rinci' => $req['rinci'],
                'mutasi' => $mutasi,
                // 'requset' => $req,
                'code' => 200

            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'code' => 410,
                'message' =>  $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ];
        }
    }
}
