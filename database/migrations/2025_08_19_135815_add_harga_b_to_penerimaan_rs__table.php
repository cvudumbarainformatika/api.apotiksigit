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
             $table->decimal('harga_b', 20, 2)->default(0)->after('jumlah_k');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('penerimaan_rs', 'harga_b')) {
            Schema::table('penerimaan_rs', function (Blueprint $table) {
                $table->dropColumn('harga_b');
            });
        }
    }
};
