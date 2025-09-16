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
        Schema::create('stok_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('nopenerimaan');
            $table->string('noorder')->nullable();
            $table->string('kode_barang');
            $table->string('nobatch')->nullable();
            $table->string('id_penerimaan_rinci')->nullable();
            $table->decimal('isi', 20, 0)->default(0);
            $table->string('satuan_b')->nullable();
            $table->string('satuan_k')->nullable();
            $table->decimal('jumlah_b', 20, 0)->default(0);
            $table->decimal('jumlah_k', 20, 0)->default(0);
            $table->decimal('harga', 20, 2)->default(0);
            $table->decimal('pajak_rupiah', 20, 2)->default(0);
            $table->integer('diskon_persen')->default(0);
            $table->decimal('diskon_rupiah', 20, 2)->default(0);
            $table->decimal('harga_total', 20, 2)->default(0);
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->date('tgl_exprd')->nullable();
            $table->dateTime('tgl_opname');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_opnames');
    }
};
