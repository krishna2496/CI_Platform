<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropForeignKeyStatusIdFromTimesheetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timesheet', function (Blueprint $table) {
            $table->dropForeign('timesheet_status_id_foreign');
            $table->dropIndex('timesheet_status_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timesheet', function (Blueprint $table) {
            $table->foreign('status_id')->references('timesheet_status_id')->on('timesheet_status')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }
}
