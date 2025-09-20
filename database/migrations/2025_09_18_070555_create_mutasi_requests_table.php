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
        Schema::create('mutasi_requests', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mutasi');
            $table->string('kode_barang');
            $table->decimal('jumlah', 20, 2)->default(0);
            $table->decimal('distribusi', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_requests');
    }
};
