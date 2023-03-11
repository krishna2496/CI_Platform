<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMissionMediaInternalNoteLimit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission_media', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission_media` CHANGE `internal_note` `internal_note` varchar(255)");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mission_media', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission_media` CHANGE `internal_note` `internal_note` varchar(60)");
        });
    }
}
