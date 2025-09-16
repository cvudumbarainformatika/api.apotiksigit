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
            $table->bigInteger('kode_kategori')->default(0)->after('kode_barang');
        });
        DB::unprepared("DROP PROCEDURE IF EXISTS kode_kategori;");

        DB::unprepared("

                CREATE DEFINER=`admin`@`%` PROCEDURE `kode_kategori`(OUT nomor INT(12))
                BEGIN

                    DECLARE jml INT DEFAULT 0;

                    DECLARE cur_query CURSOR FOR select kode_kategori from counter;

                    DECLARE CONTINUE HANDLER FOR NOT FOUND SET jml = 1;

                    OPEN cur_query;

                    WHILE (NOT jml) DO

                        FETCH cur_query INTO nomor;

                        IF NOT jml THEN

                            update counter set kode_kategori=kode_kategori+1;

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
        if (Schema::hasColumn('counter', 'kode_kategori')) {
            Schema::table('counter', function (Blueprint $table) {
                $table->dropColumn('kode_kategori');
            });
        }
        DB::unprepared("DROP PROCEDURE IF EXISTS kode_kategori;");
    }
};
