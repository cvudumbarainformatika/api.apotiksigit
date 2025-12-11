<?php

namespace App\Helpers\Send;

use App\Models\FailedToSend;
use App\Models\Master\Cabang;
use App\Models\Setting\ProfileToko;
use Illuminate\Support\Facades\Http;

class MasterHelper
{
    public static function isGundangHere()
    {
        $profile = ProfileToko::first();
        $myCabang = Cabang::where('kodecabang', $profile->kode_toko)->first();
        $countUrl = Cabang::where('url', $myCabang->url)->count();
        if ($countUrl > 1) {
            return true;
        }
        return false;
    }
    public static function sendMaster($data)
    {
        $profile = ProfileToko::first();
        $myCabang = Cabang::where('kodecabang', $profile->kode_toko)->first();
        $urls = Cabang::where('url', '!=', $myCabang->url)->pluck('url');
        $kirims = [];
        $codes = [];
        $fails = [];
        foreach ($urls as $url) {
            $resp = self::kirim($url, $data);
            $kirims[] = $resp;
            // $code = $resp['resp']['code'];
            $code = data_get($resp, 'resp.code');
            $codes[] = $code;
            if (!in_array($code, [200, 201])) {
                $ada = unserialize(serialize($data));
                $ada['response'] = $resp;
                $ada['url'] = $url;
                $fails[] = $ada;
            }
        }
        if (!empty($fails)) {
            foreach ($fails as $fail) {
                FailedToSend::updateOrCreate([
                    'kode' => $fail['kode'],
                    'model' => $fail['model'],
                    'url' => $fail['url'],
                ], [
                    'action' => $fail['action'],
                    'request' => $fail['data'],
                    'response' => $fail['response'],
                ]);
            }
        }
        return [
            'kirima' => $kirims,
            'codes' => $codes,
            'fails' => $fails,
        ];
    }
    public static function reSendMaster($request)
    {


        $success = [];
        foreach ($request as $key) {
            $data = [
                'kode' => $key->kode,
                'action' => $key->action,
                'model' => $key->model,
                'data' => $key->request,
            ];
            $resp = self::kirim($key->url, $data);
            $kirims[] = $resp;
            // $code = $resp['resp']['code'];
            $code = data_get($resp, 'resp.code');
            $codes[] = $code;
            if (in_array($code, [200, 201])) {
                $ada = unserialize(serialize($key));
                $ada['resp'] = $resp;
                $success[] = $ada;
            }
        }
        if (!empty($success)) {
            foreach ($success as $key) {
                $failed = FailedToSend::find($key->id);
                if ($failed) {
                    $failed->delete();
                }
                // return $failed;
            }
        }
        return [
            'kirima' => $kirims,
            'codes' => $codes,
            'success' => $success,
        ];
    }
    public static function kirim($url, $data)
    {
        try {
            $kirim = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($url . 'v1/master/barang/terima', $data);
            $resp = json_decode($kirim, true);
        } catch (\Exception $e) {
            // koneksi gagal (connection refused / timeout / DNS error)
            $resp = [
                'success' => false,
                'message' => 'Connection failed',
                'error'   => $e->getMessage()
            ];
        }
        return $resp;
    }
}
