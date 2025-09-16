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
        Schema::table('counter', function (Blueprint $table) {
             $table->bigInteger('nopenerimaan')->default(0)->after('nomor_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('counter', 'nopenerimaan')) {
            Schema::table('counter', function (Blueprint $table) {
                $table->dropColumn('nopenerimaan');
            });
        }
    }
};
