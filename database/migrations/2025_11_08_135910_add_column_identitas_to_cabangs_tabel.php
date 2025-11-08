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
        Schema::table('cabangs', function (Blueprint $table) {
            $table->text('identitas')->nullable()->after('footer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cabangs', 'identitas')) {
            Schema::table('cabangs', function (Blueprint $table) {
                $table->dropColumn('identitas');
            });
        }
    }
};
