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
        Schema::create('retur_pembelian_hs', function (Blueprint $table) {
            $table->id();
            $table->string('noretur');
            $table->string('nopenerimaan');
            $table->string('nofaktur');
            $table->dateTime('tglretur');
            $table->string('kode_supplier');
            $table->string('kode_user');
            $table->string('flag')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_pembelian_hs');
    }
};
