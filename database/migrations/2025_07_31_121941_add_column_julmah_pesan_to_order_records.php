<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_records', function (Blueprint $table) {
            $table->string('jumlah_pesan')->nullable()->after('kode_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order_records', 'jumlah_pesan')) {
            Schema::table('order_records', function (Blueprint $table) {
                $table->dropColumn('jumlah_pesan');
            });
        }
    }
};
