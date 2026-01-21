<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetCounter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'counter:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset Counter';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // update counter penjualan
        DB::table('counter')
            ->where('id', 1)
            ->update([
                'nopenjualan' => 0
            ]);
    }
}
