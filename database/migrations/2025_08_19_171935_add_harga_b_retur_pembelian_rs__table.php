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
            $table->decimal('harga_b',20,2)->default(0)->after('harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retur_pembelian_rs', function (Blueprint $table) {
            $table->dropColumn([
                'harga_b',
            ]);
        });
    }
};
