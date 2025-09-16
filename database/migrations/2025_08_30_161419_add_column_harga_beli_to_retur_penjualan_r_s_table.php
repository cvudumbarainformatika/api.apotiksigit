<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('retur_penjualan_rs', function (Blueprint $table) {
            $table->decimal('harga_beli', 20)->default(0)->after('harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('retur_penjualan_rs', 'harga_beli')) {
            Schema::table('retur_penjualan_rs', function (Blueprint $table) {
                $table->dropColumn('harga_beli');
            });
        }
    }
};
