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
        Schema::create('penyesuaians', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang');
            $table->dateTime('tgl_penyesuaian')->nullable();
            $table->string('transaksi')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('id_stok');
            $table->bigInteger('id_penerimaan_rinci');
            $table->decimal('jumlah_k', 20, 0)->default(0);
            $table->string('flag', 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyesuaians');
    }
};
