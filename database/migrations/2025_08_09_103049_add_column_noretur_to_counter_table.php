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
            $table->bigInteger('noretur')->default(0)->after('kode_dokter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counter', function (Blueprint $table) {
            //
        });

        if (Schema::hasColumn('counter', 'noretur')) {
            Schema::table('counter', function (Blueprint $table) {
                $table->dropColumn('noretur');
            });
        }
    }
};
