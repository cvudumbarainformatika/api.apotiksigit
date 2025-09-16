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
        if (Schema::hasColumn('retur_penjualan_rs', 'returpenjualan_h_id')) {
            Schema::table('retur_penjualan_rs', function (Blueprint $table) {
                $table->dropColumn('returpenjualan_h_id');
            });
        }
        if (Schema::hasColumn('retur_penjualan_rs', 'returpenjualan_h_noretur')) {
            Schema::table('retur_penjualan_rs', function (Blueprint $table) {
                $table->dropColumn('returpenjualan_h_noretur');
            });
        }
        if (Schema::hasColumn('penyesuaians', 'transaksi')) {
            Schema::table('penyesuaians', function (Blueprint $table) {
                $table->dropColumn('transaksi');
            });
        }
        if (Schema::hasColumn('penyesuaians', 'flag')) {
            Schema::table('penyesuaians', function (Blueprint $table) {
                $table->dropColumn('flag');
            });
        }
        Schema::table('penyesuaians', function (Blueprint $table) {
            $table->string('jumlah_sebelum')->nullable()->after('jumlah_k');
        });
        Schema::table('penyesuaians', function (Blueprint $table) {
            $table->string('jumlah_setelah')->nullable()->after('jumlah_sebelum');
        });
        Schema::table('penyesuaians', function (Blueprint $table) {
            $table->string('satuan_k')->nullable()->after('jumlah_setelah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('penyesuaians', 'jumlah_sebelum')) {
            Schema::table('penyesuaians', function (Blueprint $table) {
                $table->dropColumn('jumlah_sebelum');
            });
        }
        if (Schema::hasColumn('penyesuaians', 'jumlah_setelah')) {
            Schema::table('penyesuaians', function (Blueprint $table) {
                $table->dropColumn('jumlah_setelah');
            });
        }
        if (Schema::hasColumn('penyesuaians', 'satuan_k')) {
            Schema::table('penyesuaians', function (Blueprint $table) {
                $table->dropColumn('satuan_k');
            });
        }
        //
    }
};
