<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTenantSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_setting', function (Blueprint $table) {
            $table->bigIncrements('tenant_setting_id');
            $table->bigInteger('setting_id')->unsigned()->comment('FK ( ci_master.tenant_has_setting.tenant_setting_id)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_setting');
    }
}
