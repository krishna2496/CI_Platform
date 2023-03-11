<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnumToNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `notification` CHANGE `action` `action` ENUM('CREATED','APPROVED','REJECTED','PUBLISHED','PENDING','DECLINED','UPDATED','INVITE','AUTOMATICALLY_APPROVED','SUBMIT_FOR_APPROVAL','DELETED','REFUSED','PUBLISHED_FOR_APPLYING')");
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
            \DB::statement("ALTER TABLE `notification` CHANGE `action` `action` ENUM('CREATED','APPROVED','REJECTED','PUBLISHED','PENDING','DECLINED','INVITE','AUTOMATICALLY_APPROVED','SUBMIT_FOR_APPROVAL','DELETED','REFUSED','PUBLISHED_FOR_APPLYING')");
        });
    }
}
