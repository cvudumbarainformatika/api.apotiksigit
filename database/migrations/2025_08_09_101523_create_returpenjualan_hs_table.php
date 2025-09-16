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
        Schema::create('retur_penjualan_hs', function (Blueprint $table) {
            $table->id();
            $table->string('noretur');
            $table->string('nopenerimaan')->nullable()->default(null);
            $table->string('nofaktur')->nullable()->default(null);
            $table->dateTime('tgl_retur')->nullable()->default(now());
            $table->string('kode_supplier')->nullable()->default(null);
            $table->string('kode_user')->nullable()->default(null);
            $table->string('flag')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_penjualan_hs');
    }
};
