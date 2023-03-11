<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMissionTableOrganisationIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission` CHANGE `organisation_id` `organisation_id` char(36) NULL DEFAULT NULL;");
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
            \DB::statement("ALTER TABLE `mission` CHANGE `organisation_id` `organisation_id` varchar(36) NULL DEFAULT NULL;");
        });
    }
}
