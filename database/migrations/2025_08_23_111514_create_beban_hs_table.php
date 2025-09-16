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
        Schema::create('beban_hs', function (Blueprint $table) {
            $table->id();
            $table->string('notransaksi');
            $table->string('keterangan')->nullable()->default(null);
            $table->string('flag')->nullable()->default(null);
            $table->string('kode_user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beban_hs');
    }
};
