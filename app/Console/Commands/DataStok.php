<?php

namespace App\Console\Commands;

use App\Http\Controllers\DataMigration\CekDataController;
use Illuminate\Console\Command;

class DataStok extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrasi:stok';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data Stok';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Migrasi Data Stok');
        $data = CekDataController::migrasiDataStok();
        if (!$data['status']) {
            $this->error('❌ ' . $data['message']); // kasih info error ke terminal
            return self::FAILURE; // biar command stop
        }
        $this->info('✅ ' . $data['message']);
    }
}
