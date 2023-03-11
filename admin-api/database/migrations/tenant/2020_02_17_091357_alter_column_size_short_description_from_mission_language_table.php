<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnSizeShortDescriptionFromMissionLanguageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission_language', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission_language` CHANGE `short_description` `short_description` varchar(1000)");
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
            \DB::statement("ALTER TABLE `mission_language` CHANGE `short_description` `short_description` varchar(255)");
        });
    }
}
