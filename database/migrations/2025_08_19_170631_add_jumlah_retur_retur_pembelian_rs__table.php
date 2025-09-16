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
        Schema::table('retur_pembelian_rs', function (Blueprint $table) {
            $table->decimal('jumlahretur_b',20,0)->default(0)->after('kode_user');
            $table->decimal('jumlahretur_k',20,0)->default(0)->after('jumlahretur_b');
            $table->decimal('diskonretur_rupiah',20,2)->default(0)->after('jumlahretur_k');
            $table->decimal('pajakretur_rupiah',20,2)->default(0)->after('diskonretur_rupiah');
            $table->decimal('hargaretur_total',20,2)->default(0)->after('pajakretur_rupiah');
            $table->decimal('subtotalretur',20,2)->default(0)->after('hargaretur_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retur_pembelian_rs', function (Blueprint $table) {
            $table->dropColumn([
                'jumlahretur_b',
                'jumlahretur_k',
                'diskonretur_rupiah',
                'pajakretur_rupiah',
                'hargaretur_total',
                'subtotalretur',
            ]);
        });
    }
};
