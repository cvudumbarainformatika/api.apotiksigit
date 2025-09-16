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
            $table->string('satuan_k')->nullable();
            $table->string('satuan_b')->nullable();
            $table->integer('isi')->default(1);
            $table->string('kandungan')->nullable();
            $table->decimal('harga_jual_resep_k', 20, 2)->default(0);
            $table->decimal('harga_jual_biasa_k', 20, 2)->default(0);
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
