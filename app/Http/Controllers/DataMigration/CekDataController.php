<?php

namespace App\Http\Controllers\DataMigration;

use App\Helpers\Formating\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use App\Models\Master\Beban;
use App\Models\Master\Cabang;
use App\Models\Master\Dokter;
use App\Models\Master\Jabatan;
use App\Models\Master\Kategori;
use App\Models\Master\Merk;
use App\Models\Master\Pelanggan;
use App\Models\Master\Rak;
use App\Models\Master\Satuan;
use App\Models\Master\Supplier;
use App\Models\OldApp\Master\Beban as MasterBeban;
use App\Models\OldApp\Master\Cabang as OldCabang;
use App\Models\OldApp\Master\Customer;
use App\Models\OldApp\Master\Dokter as OldDokter;
use App\Models\OldApp\Master\Info;
use App\Models\OldApp\Master\Kategori as MasterKategori;
use App\Models\OldApp\Master\Merk as MasterMerk;
use App\Models\OldApp\Master\OldUser;
use App\Models\OldApp\Master\Product;
use App\Models\OldApp\Master\Rak as MasterRak;
use App\Models\OldApp\Master\Satuan as MasterSatuan;
use App\Models\OldApp\Master\SatuanBesar;
use App\Models\OldApp\Master\Supplier as MasterSupplier;
use App\Models\Setting\ProfileToko;
use App\Models\User;
use Carbon\Carbon;
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
         * 7. supplier, perusahaan - supplier
         * 8. rak - rak
         * 9. satuan dan satuan besar - satuan , kode - nama
         * 10. merk - merk
         * 11. product - barang
         * 12. oldUser - user
         * langkah : copy, kemudian isi counter sesuai dengan id terakhir master yang bersangkutan
         * khusus satuan dan satuan besar, maka ambil id palin besar untuk update counter
         */
        // 1. beban 
        $beban = self::migrasiDataBeban();
        // // 2. cabang
        $cabang = self::migrasiDataCabang();
        // // 3. customer
        $customer = self::migrasiDataCustomer();
        // // 4. dokter
        $dokter = self::migrasiDataDokter();
        // // 5. info
        $info = self::migrasiDataInfo();
        // // 6. ketegori
        $ketegori = self::migrasiDataKategori();
        // // 7. Perusahaan 
        $perusahaan = self::migrasiDataPerusahaan();
        // // 8. Rak 
        $rak = self::migrasiDataRak();
        // // 9. Satuan 
        $satuan = self::migrasiDataSatuan();
        // // 10. Merk 
        $merk = self::migrasiDataMerk();
        // // 11. Product 
        $product = self::migrasiDataProduct();

        // // 11.0 Jabatan User
        $jabatan = self::migrasiDataJabatan();
        // // 11.1 User
        $user = self::migrasiDataUser();

        return [
            'beban' => $beban,
            'cabang' => $cabang,
            'customer' => $customer,
            'dokter' => $dokter,
            'info' => $info,
            'ketegori' => $ketegori,
            'perusahaan' => $perusahaan,
            'rak' => $rak,
            'satuan' => $satuan,
            'merk' => $merk,
            'product' => $product,
            'jabatan' => $jabatan,
            'user' => $user,
        ];
    }
    public static function migrasiDataBeban()
    {
        /**
         * beban tidak ada counter dan tidak ada kode
         */
        $oldData = MasterBeban::get(); // karena cuma dikit
        try {
            DB::beginTransaction();
            $data = [];
            foreach ($oldData as $key) {
                $data[] = [
                    'kode' => $key['kode_beban'],
                    'nama' => $key['nama'],
                    'flag' => '',
                    'created_at' => $key['created_at'],
                    'updated_at' => $key['updated_at'],
                ];
            }
            if (!empty($data)) {
                Beban::query()->delete();
                Beban::insert($data);
            } else {
                throw new Exception('Data Beban kosong');
            }
            DB::commit();
            return [
                'message' => 'data Beban sudah di isi'
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
                    'hidden' => null,
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
                    'nama' => $key['nama'],
                    'alamat' => $key['alamat'] ?? '',
                    'hidden' => null,
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
                    'hidden' => null,
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
    public static function migrasiDataPerusahaan()
    {
        /**
         * supplier - supplier
         */
        $OdlData = MasterSupplier::with('perusahaan')->get(); // karena cuma dikit
        // ambil id terakhir
        $lastData = MasterSupplier::orderBy('id', 'DESC')->first();
        $lastId = $lastData->id;

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($OdlData as $key) {
                $data[] = [
                    'kode' => $key['kode_supplier'],
                    'nama' => $key['nama'],
                    'hidden' => null,
                    'tlp' => $key['kontak'],
                    'bank' => '-',
                    'rekening' => '-',
                    'alamat' => $key['alamat'],
                    'created_at' => $key['created_at'],
                    'updated_at' => $key['updated_at'],
                ];
            }
            if (!empty($data)) {
                Supplier::query()->delete();
                Supplier::insert($data);
                // update counter
                DB::table('counter')->update([
                    'kode_supplier' => $lastId
                ]);
            } else {
                throw new Exception('Data Supplier kosong');
            }
            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data Supplier sudah di isi',
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
    public static function migrasiDataRak()
    {
        /**
         * rak - rak
         */
        $OdlData = MasterRak::get(); // karena cuma dikit
        // ambil id terakhir
        $lastData = MasterRak::orderBy('id', 'DESC')->first();
        $lastId = $lastData->id;

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($OdlData as $key) {
                $data[] = [
                    'kode' => $key['kode_rak'] ?? $key['id'],
                    'nama' => $key['nama'],
                    'hidden' => null,
                    'created_at' => $key['created_at'],
                    'updated_at' => $key['updated_at'],
                ];
            }
            if (!empty($data)) {
                Rak::query()->delete();
                Rak::insert($data);
                // update counter
                DB::table('counter')->update([
                    'kode_rak' => $lastId
                ]);
            } else {
                throw new Exception('Data Rak kosong');
            }
            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data Rak sudah di isi',
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
    public static function migrasiDataMerk()
    {
        /**
         * rak - rak
         */
        $OdlData = MasterMerk::get(); // karena cuma dikit
        // ambil id terakhir
        $lastData = MasterMerk::orderBy('id', 'DESC')->first();
        $lastId = $lastData->id;

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($OdlData as $key) {
                $data[] = [
                    'kode' => $key['kode_rak'] ?? $key['id'],
                    'nama' => $key['nama'],
                    'hidden' => null,
                    'created_at' => $key['created_at'],
                    'updated_at' => $key['updated_at'],
                ];
            }
            if (!empty($data)) {
                Merk::query()->delete();
                Merk::insert($data);
                // update counter
                DB::table('counter')->update([
                    'kode_merk' => $lastId
                ]);
            } else {
                throw new Exception('Data Merk kosong');
            }
            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data Merk sudah di isi',
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
    public static function migrasiDataSatuan()
    {
        /**
         * satuan, satuan besar - satuan
         */
        $satuan = MasterSatuan::get(); // karena cuma dikit
        $satuanB = SatuanBesar::get(); // karena cuma dikit
        $data1 = $satuan->toArray();
        $data2 = $satuanB->toArray();
        $merged = array_merge($data1, $data2);

        $unik = [];
        foreach ($merged as $item) {
            $unik[$item['nama']] = $item; // overwrite kalau nama sama, kode ikut salah satu
        }

        $OdlData = array_values($unik);
        // count jumlah data
        // $lastData = MasterRak::orderBy('id', 'DESC')->first();
        $lastId = count($OdlData);
        // return [
        //     'lastId' => $lastId,
        //     'merged' => $merged,
        //     'OdlData' => $OdlData,
        // ];

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($OdlData as $key) {
                $data[] = [
                    'kode' => $key['kode_satuan'] ?? $key['id'],
                    'nama' => $key['nama'],
                    'hidden' => null,
                    'created_at' => Carbon::parse($key['created_at'])->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($key['updated_at'])->format('Y-m-d H:i:s'),
                ];
            }
            if (!empty($data)) {
                Satuan::query()->delete();
                Satuan::insert($data);
                // update counter
                DB::table('counter')->update([
                    'kode_satuan' => $lastId
                ]);
            } else {
                throw new Exception('Data Satuan kosong');
            }
            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data Satuan sudah di isi',
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
    public static function migrasiDataProduct()
    {
        /**
         * product - barang
         */
        $OdlData = Product::with(
            'kategori',
            'rak',
            'satuan',
            'satuanBesar',
            'merk',
        )->get(); // karena cuma dikit
        // ambil id terakhir
        $lastData = Product::orderBy('id', 'DESC')->first();
        $lastId = $lastData->id;
        // return [
        //     'lastId' => $lastId,
        //     'OdlData' => $OdlData,
        // ];

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($OdlData as $key) {
                // return $key['merk'];
                $data[] = [
                    'kode' => $key['kode_produk'],
                    'nama' => $key['nama'],
                    'hidden' => null,
                    'barcode' => $key['barcode'] ?? '',
                    'satuan_k' => $key['satuan']['nama'],
                    'satuan_b' => $key['satuanBesar']['nama'],
                    'merk' => $key['merk'] ? $key['merk']['nama'] : '',
                    'rak' => $key['rak'] ? $key['rak']['nama'] : '',
                    'kategori' => $key['kategori'] ? $key['kategori']['nama'] : '',
                    'isi' => $key['pengali'],
                    'kandungan' => '',
                    'harga_jual_umum' => $key['harga_jual_umum'],
                    'harga_jual_resep' => $key['harga_jual_resep'],
                    'harga_jual_cust' => $key['harga_jual_cust'],
                    'harga_jual_prem' => $key['harga_jual_prem'],
                    'margin_jual_umum' => 0,
                    'margin_jual_resep' => 0,
                    'margin_jual_cust' => 0,
                    'margin_jual_prem' => 0,
                    'limit_stok' => $key['limit_stok'],
                    'created_at' => Carbon::parse($key['created_at'])->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($key['updated_at'])->format('Y-m-d H:i:s'),
                ];
            }
            if (!empty($data)) {
                Barang::query()->delete();
                // Barang::insert($data);
                collect($data)->chunk(1000)->each(function ($chunk) {
                    Barang::insert($chunk->toArray());
                });
                // update counter
                DB::table('counter')->update([
                    'kode_barang' => $lastId
                ]);
            } else {
                throw new Exception('Data Barang kosong');
            }
            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data Barang sudah di isi',
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
    public static function migrasiDataJabatan()
    {
        /**
         * user->role - jabatan
         */
        $OdlData = OldUser::select('role')->groupBy('role')->get(); // karena cuma dikit
        // ambil id terakhir
        // $lastData = OldUser::orderBy('id', 'DESC')->first();
        $lastId = count($OdlData);
        // return [
        //     'OdlData' => $OdlData,
        //     'lastId' => $lastId,
        // ];

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($OdlData as $key) {
                $data[] = [
                    'kode' => $key['role'],
                    'nama' => $key['role'],
                    'hidden' => null,
                    'created_at' => $key['created_at'],
                    'updated_at' => $key['updated_at'],
                ];
            }
            if (!empty($data)) {
                Jabatan::query()->delete();
                Jabatan::insert($data);
                // update counter
                DB::table('counter')->update([
                    'kode_jabatan' => $lastId
                ]);
            } else {
                throw new Exception('Data Jabatan kosong');
            }
            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data Jabatan sudah di isi',
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
    public static function migrasiDataUser()
    {
        /**
         * user->role - jabatan
         */
        $OdlData = OldUser::get(); // karena cuma dikit
        // ambil id terakhir
        // $lastData = OldUser::orderBy('id', 'DESC')->first();
        $lastId = count($OdlData);
        // return [
        //     'OdlData' => $OdlData,
        //     'lastId' => $lastId,
        // ];

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($OdlData as $key) {
                $username = strstr($key['email'], '@', true);
                User::updateOrCreate(
                    [
                        'kode' => $key['id'],
                        'nama' => $key['name'],
                    ],
                    [
                        'hidden' => null,
                        'username' => $username,
                        'email' => $key['email'],
                        'email_verified_at' => $key['email_verified_at'],
                        'password' => $key['password'],
                        'hp' => null,
                        'alamat' => null,
                        'kode_jabatan' => $key['role'],
                        'remember_token' => $key['remember_token'],
                        'created_at' => $key['created_at'],
                        'updated_at' => $key['updated_at'],
                    ]
                );
            }

            DB::commit();
            $counter = DB::table('counter')->first();
            return [
                'message' => 'data User sudah di isi',
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
