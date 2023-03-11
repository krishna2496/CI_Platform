<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterActivityLogTableTenantCurrencyType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activity_log', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `activity_log` CHANGE `type` `type` ENUM('TENANT',
            'API_USER','API_USER_KEY_RENEW','TENANT_SETTINGS','LANGUAGE','TENANT_LANGUAGE', 'TENANT_CURRENCY')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_log', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `activity_log` CHANGE `type` `type` ENUM('TENANT',
            'API_USER','API_USER_KEY_RENEW','TENANT_SETTINGS','LANGUAGE','TENANT_LANGUAGE')");
        });
    }
}
