<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('retur_penjualan_rs', function (Blueprint $table) {
            $table->id();
            $table->string('noretur');
            $table->string('kode_barang');
            $table->string('nobatch')->nullable()->default(null);
            $table->decimal('jumlah_k', 12, 2)->nullable()->default(null);
            $table->string('satuan_k')->nullable()->default(null);
            $table->decimal('harga', 12, 2)->nullable()->default(null);
            $table->string('kode_user')->nullable()->default(null);
            $table->bigInteger('returpenjualan_h_id');
            $table->string('returpenjualan_h_noretur');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_penjualan_rs');
    }
};
