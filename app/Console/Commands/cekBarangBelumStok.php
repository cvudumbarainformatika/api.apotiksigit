<?php

namespace App\Console\Commands;

use App\Http\Controllers\DataMigration\CekDataController;
use Illuminate\Console\Command;

class cekBarangBelumStok extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'barang:stok';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek Barang yang belum ada di tabel stok';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now()->format('d M Y H:i:s') . '] Migrasi Data Stok');

        $data = CekDataController::cekDataBarangBelumAdaStok($this);

        if (!$data['status']) {
            $this->error(
                '[' . now()->format('d M Y H:i:s') . '] ❌ ' . $data['message']
            );
            return self::FAILURE;
        }

        $this->info(
            '[' . now()->format('d M Y H:i:s') . '] ✅ ' . $data['message']
        );

        return self::SUCCESS;
    }
}
