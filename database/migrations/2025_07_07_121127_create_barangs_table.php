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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode');
            $table->string('barcode');
            $table->string('satuan_k')->nullable();
            $table->string('satuan_b')->nullable();
            $table->string('merk')->nullable();
            $table->string('rak')->nullable();
            $table->string('kategori')->nullable();
            $table->integer('isi')->default(1);
            $table->string('kandungan')->nullable();
            $table->decimal('harga_beli', 20, 2)->default(0);
            $table->decimal('harga_jual_umum', 20, 2)->default(0);
            $table->decimal('harga_jual_resep', 20, 2)->default(0);
            $table->decimal('harga_jual_cust', 20, 2)->default(0);
            $table->decimal('harga_jual_prem', 20, 2)->default(0);

            $table->integer('margin_jual_umum')->default(0);
            $table->integer('margin_jual_resep')->default(0);
            $table->integer('margin_jual_cust')->default(0);
            $table->integer('margin_jual_prem')->default(0);

            $table->integer('limit_stok')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
