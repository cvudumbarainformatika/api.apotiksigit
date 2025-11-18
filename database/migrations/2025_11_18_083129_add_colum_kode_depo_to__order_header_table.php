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
        Schema::table('order_headers', function (Blueprint $table) {
            $table->string('kode_depo')->nullable()->after('tgl_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order_headers', 'kode_depo')) {
            Schema::table('order_headers', function (Blueprint $table) {
                $table->dropColumn('kode_depo');
            });
        }
    }
};
