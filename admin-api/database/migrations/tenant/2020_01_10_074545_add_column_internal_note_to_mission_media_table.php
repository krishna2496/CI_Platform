<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInternalNoteToMissionMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission_media', function (Blueprint $table) {
            $table->string('internal_note', 60)
                ->nullable()
                ->default(null)
                ->after('media_path');
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
            $table->dropColumn('internal_note');
        });
    }
}
