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
        Schema::create('failed_to_sends', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->string('action');
            $table->string('model');
            $table->string('url');
            $table->longText('request');
            $table->longText('response');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_to_sends');
    }
};
