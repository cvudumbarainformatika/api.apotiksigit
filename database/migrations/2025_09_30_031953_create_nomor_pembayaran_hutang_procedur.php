<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('counter', function (Blueprint $table) {
            $table->bigInteger('nomor_pembayaran_hutang')->default(0)->after('id'); // Adding nomor_order column to counter table
        });
        Schema::table('penerimaan_hs', function (Blueprint $table) {
            $table->string('flag_hutang', 2)->nullable()->after('hutang')->comment('tidak null jika sudah lunas'); // Adding nomor_order column to counter table
        });

        DB::unprepared("DROP PROCEDURE IF EXISTS nomor_pembayaran_hutang;");

        DB::unprepared("

                CREATE DEFINER=`admin`@`%` PROCEDURE `nomor_pembayaran_hutang`(OUT nomor INT(12))
                BEGIN

                    DECLARE jml INT DEFAULT 0;

                    DECLARE cur_query CURSOR FOR select nomor_pembayaran_hutang from counter;

                    DECLARE CONTINUE HANDLER FOR NOT FOUND SET jml = 1;

                    OPEN cur_query;

                    WHILE (NOT jml) DO

                        FETCH cur_query INTO nomor;

                        IF NOT jml THEN

                            update counter set nomor_pembayaran_hutang=nomor_pembayaran_hutang+1;

                        END IF;

                    END WHILE;



                    CLOSE cur_query;



                END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('counter', 'nomor_pembayaran_hutang')) {
            Schema::table('counter', function (Blueprint $table) {
                $table->dropColumn('nomor_pembayaran_hutang');
            });
        }
        if (Schema::hasColumn('penerimaan_hs', 'flag_hutang')) {
            Schema::table('penerimaan_hs', function (Blueprint $table) {
                $table->dropColumn('flag_hutang');
            });
        }

        DB::unprepared("DROP PROCEDURE IF EXISTS nomor_pembayaran_hutang;");
    }
};
