<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSkillTableChangeDataTypeOfTranslationsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('skill', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `skill` CHANGE `translations` `translations` JSON");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('skill', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `skill` CHANGE `translations` `translations` TEXT");
        });
    }
}
