<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\Transactions\StokOpnameController;
use Illuminate\Console\Command;

class StokOpname extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stok:opname';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stok Opname Otomatis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        info('mulai stok opname');
        $data = StokOpnameController::opnameOtomatis();

        info($data);
    }
}
