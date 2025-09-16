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
            $table->bigInteger('kode_dokter')->default(0)->after('nopenerimaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('counter', 'kode_dokter')) {
            Schema::table('counter', function (Blueprint $table) {
                $table->dropColumn('kode_dokter');
            });
        }
    }
};
