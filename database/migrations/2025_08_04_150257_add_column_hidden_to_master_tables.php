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
        Schema::table('users', function (Blueprint $table) {
            $table->string('hidden')->nullable()->after('kode');
        });

        Schema::table('barangs', function (Blueprint $table) {
            $table->string('hidden')->nullable()->after('kode');
        });
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->string('hidden')->nullable()->after('kode');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('hidden')->nullable()->after('kode');
        });
        Schema::table('jabatans', function (Blueprint $table) {
            $table->string('hidden')->nullable()->after('kode');
        });
        Schema::table('satuans', function (Blueprint $table) {
            $table->string('hidden')->nullable()->after('kode');
        });
        Schema::table('dokters', function (Blueprint $table) {
            $table->string('hidden')->nullable()->after('kode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('dokters', 'hidden')) {
            Schema::table('dokters', function (Blueprint $table) {
                $table->dropColumn('hidden');
            });
        }
        if (Schema::hasColumn('satuans', 'hidden')) {
            Schema::table('satuans', function (Blueprint $table) {
                $table->dropColumn('hidden');
            });
        }
        if (Schema::hasColumn('jabatans', 'hidden')) {
            Schema::table('jabatans', function (Blueprint $table) {
                $table->dropColumn('hidden');
            });
        }
        if (Schema::hasColumn('suppliers', 'hidden')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('hidden');
            });
        }
        if (Schema::hasColumn('pelanggans', 'hidden')) {
            Schema::table('pelanggans', function (Blueprint $table) {
                $table->dropColumn('hidden');
            });
        }
        if (Schema::hasColumn('barangs', 'hidden')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->dropColumn('hidden');
            });
        }
        if (Schema::hasColumn('users', 'hidden')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('hidden');
            });
        }
    }
};
