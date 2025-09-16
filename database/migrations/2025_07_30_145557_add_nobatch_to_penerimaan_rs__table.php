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
        Schema::table('penerimaan_rs', function (Blueprint $table) {
            $table->string('nobatch')->default(0)->after('kode_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('penerimaan_rs', 'nobatch')) {
            Schema::table('penerimaan_rs', function (Blueprint $table) {
                $table->dropColumn('nobatch');
            });
        }
    }
};
