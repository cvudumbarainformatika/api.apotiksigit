<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Send\MasterHelper;
use App\Http\Controllers\Controller;
use App\Models\FailedToSend;
use App\Models\Master\Barang;
use App\Models\Master\Beban;
use App\Models\Master\Dokter;
use App\Models\Master\Jabatan;
use App\Models\Master\Kategori;
use App\Models\Master\KategoriExpired;
use App\Models\Master\Merk;
use App\Models\Master\Pelanggan;
use App\Models\Master\Rak;
use App\Models\Master\Satuan;
use App\Models\Master\Supplier;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CurlMasterController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = [
            [
                'name' => Barang::class,
                'sring' => 'barang',
            ],
            [
                'name' => Beban::class,
                'sring' => 'beban',
            ],
            [
                'name' => Dokter::class,
                'sring' => 'dokter',
            ],
            [
                'name' => Jabatan::class,
                'sring' => 'jabatan',
            ],
            [
                'name' => Kategori::class,
                'sring' => 'kategori',
            ],
            [
                'name' => KategoriExpired::class,
                'sring' => 'kategoriExpired',
            ],
            [
                'name' => Merk::class,
                'sring' => 'merk',
            ],
            [
                'name' => Pelanggan::class,
                'sring' => 'pelanggan',
            ],
            [
                'name' => Rak::class,
                'sring' => 'rak',
            ],
            [
                'name' => Satuan::class,
                'sring' => 'satuan',
            ],
            [
                'name' => Supplier::class,
                'sring' => 'supplier',
            ],
        ];
    }
    public function terimaMaster(Request $request)
    {
        if ($request->action == 'simpan') {
            $resp = $this->simpan($request);
            return new JsonResponse([
                'req' => $request->all(),
                'action' => 'simpan',
                'resp' => $resp
            ]);
        } else {
            $resp = $this->hapus($request);
            return new JsonResponse([
                'req' => $request->all(),
                'action' => 'hapus',
                'resp' => $resp
            ]);
        }
    }
    public function simpan($req)
    {
        $data = $req['data'];
        $str = $req['model'];
        $keys = array_column($this->model, 'sring');
        $ind = array_search($str, $keys);
        $resp = $this->model[$ind]['name']::updateOrCreate(
            [
                'kode' =>  $data['kode']
            ],
            $data
        );
        if ($resp->wasRecentlyCreated) {
            return [
                'message' => 'Data telah dibuat',
                'code' => 201
            ];
        } else {
            return [
                'message' => 'Data telah di update',
                'code' => 200
            ];
        }
    }
    public function hapus($req)
    {
        $data = $req['data'];
        $str = $req['model'];
        $keys = array_column($this->model, 'sring');
        $ind = array_search($str, $keys);
        $resp = $this->model[$ind]['name']::where('kode', $data['kode'])->first();
        if (!$resp) {
            return [
                'message' => 'Data ' . $str . ' tidak ditemukan',
                'code' => 410
            ];
        }
        $resp->update(['hidden' => '1']);
        return [
            'message' => 'Data barang sudah di hapus',
            'code' => 200
        ];
    }
    public function reSendAll()
    {
        $data = FailedToSend::get();
        $resp = MasterHelper::reSendMaster($data);
        return new JsonResponse([
            'resp' => $resp,
            'data' => $data,
        ]);
    }
}
