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
        Schema::table('counter', function (Blueprint $table) {
             $table->bigInteger('noretur_penjualan')->default(0)->after('noretur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('counter', 'noretur_penjualan')) {
            Schema::table('counter', function (Blueprint $table) {
                $table->dropColumn('noretur_penjualan');
            });
        }
    }
};
