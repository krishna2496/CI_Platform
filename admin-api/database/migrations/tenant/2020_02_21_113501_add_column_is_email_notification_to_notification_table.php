<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsEmailNotificationToNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `notification` add `is_email_notification` TINYINT(1) NOT NULL DEFAULT '0' AFTER `action`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `user` DROP `is_email_notification`");
        });
    }
}
