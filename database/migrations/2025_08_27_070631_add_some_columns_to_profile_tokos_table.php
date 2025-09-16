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
        Schema::table('profile_tokos', function (Blueprint $table) {
            $table->bigInteger('pajak')->nullable()->default(0)->after('footer');
            $table->string('foto')->nullable()->after('pajak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('profile_tokos', 'pajak')) {
            Schema::table('profile_tokos', function (Blueprint $table) {
                $table->dropColumn('pajak');
            });
        }
        if (Schema::hasColumn('profile_tokos', 'foto')) {
            Schema::table('profile_tokos', function (Blueprint $table) {
                $table->dropColumn('foto');
            });
        }
    }
};
