<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnStatusIdInTimesheetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timesheet', function (Blueprint $table) { 
            \DB::statement("ALTER TABLE `timesheet` CHANGE `status_id` `status` ENUM('PENDING','APPROVED','DECLINED','AUTOMATICALLY_APPROVED','SUBMIT_FOR_APPROVAL') NULL DEFAULT 'PENDING'");
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
            \DB::statement("ALTER TABLE `timesheet` CHANGE `status` `status_id` BIGINT UNSIGNED NULL DEFAULT NULL");
        });
    }
}
