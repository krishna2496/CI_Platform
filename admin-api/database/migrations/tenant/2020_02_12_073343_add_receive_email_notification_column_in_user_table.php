<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReceiveEmailNotificationColumnInUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `user` add `receive_email_notification` TINYINT(1) NOT NULL DEFAULT '0' AFTER `cookie_agreement_date`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `user` DROP `receive_email_notification`");
        });
    }
}
