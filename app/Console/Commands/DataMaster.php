<?php

namespace App\Console\Commands;

use App\Http\Controllers\DataMigration\CekDataController;
use Illuminate\Console\Command;

class DataMaster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrasi:master';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi Data Master';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1 beban
        $this->info('Migrasi Data Beban');
        $beban = CekDataController::migrasiDataBeban();
        if (!$beban['status']) {
            $this->error('❌ ' . $beban['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $beban['message']);
        /////

        // 2 cabang
        $this->info('Migrasi Data Cabang');
        $cabang = CekDataController::migrasiDataCabang();
        if (!$cabang['status']) {
            $this->error('❌ ' . $cabang['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $cabang['message']);
        /////

        // 3 customer
        $this->info('Migrasi Data Customer');
        $customer = CekDataController::migrasiDataCustomer();
        if (!$customer['status']) {
            $this->error('❌ ' . $customer['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $customer['message']);
        /////

        // 4. dokter
        $this->info('Migrasi Data Dokter');
        $dokter = CekDataController::migrasiDataDokter();
        if (!$dokter['status']) {
            $this->error('❌ ' . $dokter['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $dokter['message']);
        /////

        // 5. info
        $this->info('Migrasi Data Info');
        $info = CekDataController::migrasiDataInfo();
        if (!$info['status']) {
            $this->error('❌ ' . $info['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $info['message']);
        /////

        //  6. ketegori
        $this->info('Migrasi Data Kategori');
        $ketegori = CekDataController::migrasiDataKategori();
        if (!$ketegori['status']) {
            $this->error('❌ ' . $ketegori['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $ketegori['message']);
        /////

        //  7. Perusahaan 
        $this->info('Migrasi Data Perusahaan');
        $perusahaan = CekDataController::migrasiDataPerusahaan();
        if (!$perusahaan['status']) {
            $this->error('❌ ' . $perusahaan['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $perusahaan['message']);
        /////

        //  8. Rak 
        $this->info('Migrasi Data Rak');
        $rak = CekDataController::migrasiDataRak();
        if (!$rak['status']) {
            $this->error('❌ ' . $rak['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $rak['message']);
        /////

        // 9. Satuan 
        $this->info('Migrasi Data Satuan');
        $satuan = CekDataController::migrasiDataSatuan();
        if (!$satuan['status']) {
            $this->error('❌ ' . $satuan['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $satuan['message']);
        /////

        // 9. Merk 
        $this->info('Migrasi Data Merk');
        $merk = CekDataController::migrasiDataMerk();
        if (!$merk['status']) {
            $this->error('❌ ' . $merk['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $merk['message']);
        /////

        // 10. Product 
        $this->info('Migrasi Data Product');
        $product = CekDataController::migrasiDataProduct();
        if (!$product['status']) {
            $this->error('❌ ' . $product['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $product['message']);
        /////

        // 11.0 Jabatan User 
        $this->info('Migrasi Data Jabatan User');
        $jabatan = CekDataController::migrasiDataJabatan();
        if (!$jabatan['status']) {
            $this->error('❌ ' . $jabatan['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $jabatan['message']);
        /////

        // 11.1 User
        $this->info('Migrasi Data User');
        $user = CekDataController::migrasiDataUser();
        if (!$user['status']) {
            $this->error('❌ ' . $user['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $user['message']);
        /////

    }
}
