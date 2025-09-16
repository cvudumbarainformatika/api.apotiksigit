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
        Schema::table('stoks', function (Blueprint $table) {
            // bikin index composite kode_barang + id
            $table->index(['kode_barang', 'id'], 'idx_stoks_kode_id');
        });

        Schema::table('penjualan_r_s', function (Blueprint $table) {
            $table->decimal('hpp', 20)->default(0)->after('harga_beli');
        });
        Schema::table('retur_penjualan_rs', function (Blueprint $table) {
            $table->decimal('hpp', 20)->default(0)->after('harga_beli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stoks', function (Blueprint $table) {
            // rollback: drop index
            $table->dropIndex('idx_stoks_kode_id');
        });
        if (Schema::hasColumn('penjualan_r_s', 'hpp')) {
            Schema::table('penjualan_r_s', function (Blueprint $table) {
                $table->dropColumn('hpp');
            });
        }
        if (Schema::hasColumn('retur_penjualan_rs', 'hpp')) {
            Schema::table('retur_penjualan_rs', function (Blueprint $table) {
                $table->dropColumn('hpp');
            });
        }
    }
};
