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
        Schema::create('mutasi_approveds', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mutasi');
            $table->string('kode_barang');
            $table->string('nopenerimaan');
            $table->string('nobatch');
            $table->decimal('jumlah', 20, 2)->default(0);
            $table->decimal('harga', 20, 2)->default(0);
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->date('exprd', 20, 2)->nullable();
            $table->integer('id_stok_asal'); // id stok pengirim
            $table->integer('id_stok_terima')->nullable(); // id stok penerima
            $table->integer('id_penerimaan_rinci_gudang'); // id rinci penerimaan dari pegirim, nanti ini disimpan sebagai id_rinci_penerimaan di table stok, dan jika ada mutasi baru ditambahkan di id_penerimaan_rinci yang sama
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_approveds');
    }
};
