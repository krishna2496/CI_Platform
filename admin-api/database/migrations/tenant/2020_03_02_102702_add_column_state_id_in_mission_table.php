<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStateIdInMissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission` ADD `state_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `city_id`");
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
            \DB::statement("ALTER TABLE `mission` DROP `state_id`");
        });
    }
}
