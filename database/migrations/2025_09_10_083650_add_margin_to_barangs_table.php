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
        Schema::table('barangs', function (Blueprint $table) {
            $table->bigInteger('persen_biasa')->nullable()->default(0)->after('harga_jual_biasa_k');
            $table->bigInteger('persen_resep')->nullable()->default(0)->after('persen_biasa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('barangs', 'persen_biasa')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->dropColumn('persen_biasa');
            });
        }
        if (Schema::hasColumn('barangs', 'persen_resep')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->dropColumn('persen_resep');
            });
        }
    }
};
