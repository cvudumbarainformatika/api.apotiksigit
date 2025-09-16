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
        Schema::create('penerimaan_hs', function (Blueprint $table) {
            $table->id();
            $table->string('nopenerimaan')->nullable();
            $table->string('noorder')->nullable();
            $table->date('tgl_penerimaan')->default(now());
            $table->string('nofaktur')->nullable();
            $table->date('tgl_faktur')->nullable();
            $table->string('kode_suplier')->nullable();
            $table->string('jenispajak')->nullable();
            $table->string('pajak')->nullable();
            $table->string('kode_user')->nullable();
            $table->string('flag')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_hs');
    }
};
