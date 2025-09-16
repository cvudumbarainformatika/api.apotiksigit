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
        Schema::table('order_headers', function (Blueprint $table) {
            $table->string('status_penerimaan')->nullable()->default(null)->after('flag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order_headers', 'status_penerimaan')) {
            Schema::table('order_headers', function (Blueprint $table) {
                $table->dropColumn('status_penerimaan');
            });
        }
    }
};
