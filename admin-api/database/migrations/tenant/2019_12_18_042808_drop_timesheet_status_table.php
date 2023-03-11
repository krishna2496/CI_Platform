<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTimesheetStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timesheet_status', function (Blueprint $table) {
            \DB::statement("DROP TABLE IF EXISTS timesheet_status");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('timesheet_status', function (Blueprint $table) {
            $table->bigIncrements('timesheet_status_id');
            $table->string('status', 255);
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
