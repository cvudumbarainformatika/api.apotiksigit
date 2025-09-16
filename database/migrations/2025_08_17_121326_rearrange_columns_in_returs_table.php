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
        if (Schema::hasColumn('retur_penjualan_hs', 'nopenerimaan')) {
            Schema::table('retur_penjualan_hs', function (Blueprint $table) {
                $table->dropColumn('nopenerimaan');
            });
        }
        if (Schema::hasColumn('retur_penjualan_hs', 'nofaktur')) {
            Schema::table('retur_penjualan_hs', function (Blueprint $table) {
                $table->dropColumn('nofaktur');
            });
        }
        if (Schema::hasColumn('retur_penjualan_hs', 'kode_supplier')) {
            Schema::table('retur_penjualan_hs', function (Blueprint $table) {
                $table->dropColumn('kode_supplier');
            });
        }
        Schema::table('retur_penjualan_hs', function (Blueprint $table) {
            $table->string('nopenjualan')->nullable()->after('noretur');
        });
        Schema::table('retur_penjualan_rs', function (Blueprint $table) {
            $table->string('nopenjualan')->nullable()->after('noretur');
        });
        Schema::table('retur_penjualan_rs', function (Blueprint $table) {
            $table->string('id_stok')->nullable()->after('harga');
        });
        Schema::table('retur_penjualan_rs', function (Blueprint $table) {
            $table->string('id_penerimaan_rinci')->nullable()->after('id_stok');
        });
        Schema::table('retur_penjualan_rs', function (Blueprint $table) {
            $table->string('nopenerimaan')->nullable()->after('nobatch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retur_penjualan_hs', function (Blueprint $table) {
            $table->string('nopenerimaan')->nullable()->after('noretur');
        });
        Schema::table('retur_penjualan_hs', function (Blueprint $table) {
            $table->string('nofaktur')->nullable()->after('nopenerimaan');
        });
        Schema::table('retur_penjualan_hs', function (Blueprint $table) {
            $table->string('kode_supplier')->nullable()->after('tgl_retur');
        });
        if (Schema::hasColumn('retur_penjualan_hs', 'nopenjualan')) {
            Schema::table('retur_penjualan_hs', function (Blueprint $table) {
                $table->dropColumn('nopenjualan');
            });
        }
        if (Schema::hasColumn('retur_penjualan_rs', 'nopenjualan')) {
            Schema::table('retur_penjualan_rs', function (Blueprint $table) {
                $table->dropColumn('nopenjualan');
            });
        }
        if (Schema::hasColumn('retur_penjualan_rs', 'id_stok')) {
            Schema::table('retur_penjualan_rs', function (Blueprint $table) {
                $table->dropColumn('id_stok');
            });
        }
        if (Schema::hasColumn('retur_penjualan_rs', 'id_penerimaan_rinci')) {
            Schema::table('retur_penjualan_rs', function (Blueprint $table) {
                $table->dropColumn('id_penerimaan_rinci');
            });
        }
        if (Schema::hasColumn('retur_penjualan_rs', 'nopenerimaan')) {
            Schema::table('retur_penjualan_rs', function (Blueprint $table) {
                $table->dropColumn('nopenerimaan');
            });
        }
    }
};
