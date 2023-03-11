<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMissionLanguageTableChangeDataTypeOfDescriptionColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission_language', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission_language` CHANGE `description` `description` JSON");
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mission_language', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission_language` CHANGE `description` `description` TEXT");
       });
    }
}
