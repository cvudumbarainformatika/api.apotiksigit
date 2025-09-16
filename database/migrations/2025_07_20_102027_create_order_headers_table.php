<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Flagging : null = draft, 1 = kunci
     */
    public function up(): void
    {
        Schema::create('order_headers', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_order'); // Data Default From Procedure nomor_order(), Contoh Nilai TRX000001
            $table->date('tgl_order')->default(now());
            $table->string('flag')->nullable()->default(null);
            $table->string('kode_user');
            $table->string('kode_supplier');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_headers');
    }
};
