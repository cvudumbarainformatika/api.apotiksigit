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
        Schema::create('pembayaran_hutang_rincis', function (Blueprint $table) {
            $table->id();
            $table->string('nopelunasan');
            $table->string('noorder');
            $table->string('nopenerimaan');
            $table->string('nofaktur');
            $table->string('kode_suplier');
            $table->decimal('nominal', 20, 2)->comment('subtotal penerimaan');
            $table->decimal('pajak', 20, 2)->comment('total pajak dalam rupiah');
            $table->decimal('diskon', 20, 2)->comment('total diskon dalam rupian');
            $table->decimal('total', 20, 2)->comment('total setelah ditambah pajak dan dikurangi diskon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_hutang_rincis');
    }
};
