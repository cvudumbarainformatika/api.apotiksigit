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
        Schema::create('mutasi_headers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mutasi');
            $table->string('pengirim');
            $table->string('dari');
            $table->string('tujuan');
            $table->string('penerima')->nullable();
            $table->dateTime('tgl_permintaan')->nullable();
            $table->dateTime('tgl_distribusi')->nullable();
            $table->dateTime('tgl_terima')->nullable();
            $table->string('status', 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_headers');
    }
};
