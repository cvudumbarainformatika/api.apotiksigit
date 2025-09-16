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
            $table->bigInteger('nomor_order')->default(0)->after('kode_jabatan'); // Adding nomor_order column to counter table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('counter', 'nomor_order')) {
            Schema::table('counter', function (Blueprint $table) {
                $table->dropColumn('nomor_order');
            });
        }
    }
};
