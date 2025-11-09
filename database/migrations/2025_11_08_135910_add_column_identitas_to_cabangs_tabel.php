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
            $table->text('identitas')->nullable()->after('namacabang');
            $table->string('url')->nullable()->after('identitas');
            $table->string('security')->nullable()->after('url');
        });
        Schema::table('mutasi_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('mutasi_header_id')->nullable()->after('id');
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
        if (Schema::hasColumn('cabangs', 'url')) {
            Schema::table('cabangs', function (Blueprint $table) {
                $table->dropColumn('url');
            });
        }
        if (Schema::hasColumn('cabangs', 'security')) {
            Schema::table('cabangs', function (Blueprint $table) {
                $table->dropColumn('security');
            });
        }
        if (Schema::hasColumn('mutasi_requests', 'mutasi_header_id')) {
            Schema::table('mutasi_requests', function (Blueprint $table) {
                $table->dropColumn('mutasi_header_id');
            });
        }
    }
};
