<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateThemeIdOnMissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission` MODIFY COLUMN `theme_id` bigint(20) unsigned NULL");
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
            \DB::statement("ALTER TABLE `mission` MODIFY COLUMN `theme_id` bigint(20) unsigned NOT NULL");
        });
    }
}
