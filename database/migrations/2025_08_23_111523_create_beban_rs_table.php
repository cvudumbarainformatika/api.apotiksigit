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
        Schema::create('beban_rs', function (Blueprint $table) {
            $table->id();
            $table->string('notransaksi');
            $table->string('kode_beban');
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->string('kode_user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beban_rs');
    }
};
