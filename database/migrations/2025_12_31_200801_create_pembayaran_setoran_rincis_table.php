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
        Schema::create('pembayaran_setoran_rincis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pembayaran_setoran_id')->index();
            $table->string('notransaksi')->nullable()->index();
            $table->string('nopenjualan')->index();
            $table->bigInteger('nominal_transaksi');
            $table->bigInteger('nominal_cash'); // 
            $table->bigInteger('nominal_retur'); // 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_setoran_rincis');
    }
};
