<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        DB::unprepared("DROP PROCEDURE IF EXISTS noretur;");

        DB::unprepared("

                CREATE DEFINER=`admin`@`%` PROCEDURE `noretur`(OUT nomor INT(12))
                BEGIN

                    DECLARE jml INT DEFAULT 0;

                    DECLARE cur_query CURSOR FOR select noretur from counter;

                    DECLARE CONTINUE HANDLER FOR NOT FOUND SET jml = 1;

                    OPEN cur_query;

                    WHILE (NOT jml) DO

                        FETCH cur_query INTO nomor;

                        IF NOT jml THEN

                            update counter set noretur=noretur+1;

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
        //
        DB::unprepared("DROP PROCEDURE IF EXISTS noretur;");
    }
};
