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
        Schema::create('penjualan_r_s', function (Blueprint $table) {
            $table->id();
            $table->string('nopenjualan')->nullable();
            $table->unsignedBigInteger('id_penerimaan_rinci')->nullable();
            $table->unsignedBigInteger('id_stok')->nullable();
            $table->string('nopenerimaan')->nullable();
            $table->string('nobatch')->nullable();
            $table->string('kode_barang')->nullable();
            $table->decimal('jumlah_k', 20, 0)->default(0);
            $table->decimal('jumlah_b', 20, 0)->default(0);
            $table->integer('isi')->default(1);
            $table->string('satuan_k')->nullable();
            $table->string('satuan_b')->nullable();
            $table->date('tgl_exprd')->nullable();
            $table->decimal('harga_jual', 20, 0)->default(0);
            $table->decimal('harga_beli', 20, 0)->default(0);
            $table->decimal('subtotal', 20, 0)->default(0);
            $table->string('kode_user')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_r_s');
    }
};
