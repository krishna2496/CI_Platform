<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMissionTableChangeTypeOfMissionTypeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission` CHANGE `mission_type` `mission_type` ENUM('TIME','GOAL','DONATION','EAF','DISASTER_RELIEF')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mission', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission` CHANGE `mission_type` `mission_type` ENUM('TIME','GOAL')");
        });
    }
}
