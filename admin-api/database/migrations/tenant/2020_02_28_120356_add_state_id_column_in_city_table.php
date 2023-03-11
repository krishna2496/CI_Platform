<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStateIdColumnInCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('city', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `city` ADD `state_id` BIGINT UNSIGNED DEFAULT NULL AFTER `city_id`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('city', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `city` DROP `state_id`");
        });
    }
}
