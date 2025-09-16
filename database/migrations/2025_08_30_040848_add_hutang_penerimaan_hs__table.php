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
        Schema::table('penerimaan_hs', function (Blueprint $table) {
            $table->string('hutang')->default('')->after('flag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('penerimaan_hs', 'hutang')) {
            Schema::table('penerimaan_hs', function (Blueprint $table) {
                $table->dropColumn('hutang');
            });
        }
    }
};
