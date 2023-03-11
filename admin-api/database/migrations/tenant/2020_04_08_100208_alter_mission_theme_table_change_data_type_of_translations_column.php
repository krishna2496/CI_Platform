<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMissionThemeTableChangeDataTypeOfTranslationsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission_theme', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission_theme` CHANGE `translations` `translations` JSON");
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mission_theme', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission_theme` CHANGE `translations` `translations` TEXT");
       });
    }
}
