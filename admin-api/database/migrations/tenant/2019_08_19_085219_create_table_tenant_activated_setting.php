<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTenantActivatedSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_activated_setting', function (Blueprint $table) {
            $table->bigIncrements('tenant_activated_setting_id');
            $table->unsignedBigInteger('tenant_setting_id');
            $table->timestamps();
            $table->softDeletes();
			$table->foreign('tenant_setting_id')->references('tenant_setting_id')->on('tenant_setting')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_activated_setting');
    }
}
