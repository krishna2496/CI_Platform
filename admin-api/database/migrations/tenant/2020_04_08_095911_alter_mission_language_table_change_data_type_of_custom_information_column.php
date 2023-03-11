<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMissionLanguageTableChangeDataTypeOfCustomInformationColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission_language', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission_language` CHANGE `custom_information` `custom_information` JSON");
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
            \DB::statement("ALTER TABLE `mission_language` CHANGE `custom_information` `custom_information` TEXT");
        });
    }
}
