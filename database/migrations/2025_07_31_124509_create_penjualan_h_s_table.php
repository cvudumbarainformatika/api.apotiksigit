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
        Schema::create('penjualan_h_s', function (Blueprint $table) {
            $table->id();
            $table->string('nopenjualan')->nullable();
            $table->dateTime('tgl_penjualan')->nullable();
            $table->string('kode_pelanggan')->nullable();
            $table->string('kode_dokter')->nullable();
            $table->string('kode_user')->nullable();
            $table->string('cara_bayar')->dafault('tunai');
            $table->string('flag')->nullable();
            $table->decimal('diskon', 20, 0)->default(0);
            $table->decimal('jumlah_bayar', 20, 0)->default(0);
            $table->decimal('kembali', 20, 0)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_h_s');
    }
};
