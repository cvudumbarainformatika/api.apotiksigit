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
            $table->bigInteger('nopenjualan')->default(0)->after('nomor_order'); // Adding nopenjualan column to counter table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('counter', 'nopenjualan')) {
            Schema::table('counter', function (Blueprint $table) {
                $table->dropColumn('nopenjualan');
            });
        }
    }
};
