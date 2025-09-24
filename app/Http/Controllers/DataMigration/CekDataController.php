<?php

namespace App\Http\Controllers\DataMigration;

use App\Http\Controllers\Controller;
use App\Models\Master\Cabang;
use App\Models\Master\Dokter;
use App\Models\Master\Kategori;
use App\Models\Master\Pelanggan;
use App\Models\OldApp\Master\Cabang as OldCabang;
use App\Models\OldApp\Master\Customer;
use App\Models\OldApp\Master\Dokter as OldDokter;
use App\Models\OldApp\Master\Info;
use App\Models\OldApp\Master\Kategori as MasterKategori;
use App\Models\OldApp\Master\Product;
use App\Models\Setting\ProfileToko;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CekDataController extends Controller
{
    //


    public function index()
    {
        /**
         * 1. beban - beban => tidak ada counter
         * 2. cabang - cabang => tidak ada counter
         * 3. customer - pelanggan
         * 4. dokter - dokter
         * 5. info - setting, profile toko , kodecabang - kode_toko
         * 6. ketegori - kategori
         * 7. oldUser - user
         * 8. perusahaan - supplier
         * 9. rak - rak
         * 10. satuan dan satuan besar - satuan , kode - nama
         * 11. product - barang
         * langkah : copy, kemudian isi counter sesuai dengan id terakhir master yang bersangkutan
         * khusus satuan dan satuan besar, maka ambil id palin besar untuk update counter
         */
        // 1. beban 
        // $beban = null;
        // // 2. cabang
        // $cabang = self::migrasiDataCabang();
        // // 3. customer
        // $customer = self::migrasiDataCustomer();
        // // 4. dokter
        // $dokter = self::migrasiDataDokter();
        // // 5. info
        // $info = self::migrasiDataInfo();
        // // 6. ketegori
        // $ketegori = self::migrasiDataKategori();

        // // 7.0 Jabatan User
        // $jabatan = self::migrasiDataKategori();
        // // 7.1 User
        // $ketegori = self::migrasiDataKategori();
        return [
            // 'beban' => $beban,
            // 'cabang' => $cabang,
            // 'customer' => $customer,
            // 'dokter' => $dokter,
            // 'info' => $info,
            // 'ketegori' => $ketegori,
        ];
    }
    public static function migrasiDataBeban()
    {
        /**
         * beban tidak ada counter dan tidak ada kode
         */
    }
    public static function migrasiDataInfo()
    {
        /**
         * info - profile tok
         */
        $old = Info::first(); // karena cuma dikit
        // return $old;
        try {
            DB::beginTransaction();
            if ($old) {
                $profile = ProfileToko::updateOrCreate([
                    'id' => $old->id
                ], [
                    'nama' => $old->infos['nama'],
                    'alamat' => $old->infos['alamat'],
                    'telepon' => $old->infos['tlp'],
                    'pemilik' => $old->infos['pemilik'],
                    'kode_toko' => $old->kodecabang,

                ]);
            } else {
                throw new Exception('tidak ada data info');
            }
            DB::commit();
            return [
                'message' => 'data Profile sudah di isi',
                'profile' => $profile ?? null,
            ];
        } catch (\Throwable $th) {

            DB::rollBack();
            return [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ];
        }
    }
    public static function migrasiDataCabang()
    {
        /**
         * cabang tidak ada counter biasanya di isi manual ?? apa dari cloud ya?
         */
        $oldCabang = OldCabang::get(); // karena cuma dikit
        try {
            DB::beginTransaction();
            $data = [];
            foreach ($oldCabang as $key) {
                $data[] = [
                    'kodecabang' => $key['kodecabang'],
                    'namacabang' => $key['namacabang'],
                    'created_at' => $key['created_at'],
                    'updated_at' => $key['updated_at'],
                ];
            }
            if (!empty($data)) {
                Cabang::query()->delete();
                Cabang::insert($data);
            } else {
                throw new Exception('Data Cabang kosong');
            }
            DB::commit();
            return [
                'message' => 'data Cabang sudah di isi'
            ];
        } catch (\Throwable $th) {

            DB::rollBack();
            return [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ];
        }
    }
    public static function migrasiDataCustomer()
    {
        /**
         * customer - pelanggan
         */
        $customer = Customer::get(); // karena cuma dikit
        // ambil id terakhir
        $lastData = Customer::orderBy('id', 'DESC')->first();
        $lastId = $lastData->id;

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($customer as $key) {
                $data[] = [
                    'kode' => $key['kode_customer'],
                    'nama' => $key['nama'],
                    'alamat' => $key['alamat'],
                    'tlp' => $key['kontak'],
                    'hidden' => '',
                    'created_at' => $key['created_at'],
                    'updated_at' => $key['updated_at'],
                ];
            }
            if (!empty($data)) {
                Pelanggan::query()->delete();
                Pelanggan::insert($data);
                // update counter
                DB::table('counter')->update([
                    'kode_pelanggan' => $lastId
                ]);
            } else {
                throw new Exception('Data Pelanggan kosong');
            }
            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data Pelanggan sudah di isi',
                'lastId' => $lastId,
                'counter' => $counter,
            ];
        } catch (\Throwable $th) {

            DB::rollBack();
            return [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ];
        }
    }
    public static function migrasiDataDokter()
    {
        /**
         * dokter - dokter
         */
        $dokter = OldDokter::get(); // karena cuma dikit
        // ambil id terakhir
        $lastData = OldDokter::orderBy('id', 'DESC')->first();
        $lastId = $lastData->id;

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($dokter as $key) {
                $data[] = [
                    'kode' => $key['kode_dokter'],
                    'nama_dokter' => $key['nama'],
                    'alamat' => $key['alamat'] ?? '',
                    'hidden' => '',
                    'created_at' => $key['created_at'],
                    'updated_at' => $key['updated_at'],
                ];
            }
            if (!empty($data)) {
                Dokter::query()->delete();
                Dokter::insert($data);
                // update counter
                DB::table('counter')->update([
                    'kode_dokter' => $lastId
                ]);
            } else {
                throw new Exception('Data Dokter kosong');
            }
            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data Dokter sudah di isi',
                'lastId' => $lastId,
                'counter' => $counter,
            ];
        } catch (\Throwable $th) {

            DB::rollBack();
            return [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ];
        }
    }
    public static function migrasiDataKategori()
    {
        /**
         * ketegori - kategori
         */
        $dokter = MasterKategori::get(); // karena cuma dikit
        // ambil id terakhir
        $lastData = MasterKategori::orderBy('id', 'DESC')->first();
        $lastId = $lastData->id;

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($dokter as $key) {
                $data[] = [
                    'kode' => $key['kode_kategory'],
                    'nama' => $key['nama'],
                    'hidden' => '',
                    'created_at' => $key['created_at'],
                    'updated_at' => $key['updated_at'],
                ];
            }
            if (!empty($data)) {
                Kategori::query()->delete();
                Kategori::insert($data);
                // update counter
                DB::table('counter')->update([
                    'kode_kategori' => $lastId
                ]);
            } else {
                throw new Exception('Data Kategori kosong');
            }
            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data Kategori sudah di isi',
                'lastId' => $lastId,
                'counter' => $counter,
            ];
        } catch (\Throwable $th) {

            DB::rollBack();
            return [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ];
        }
    }
}
