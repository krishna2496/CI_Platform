<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantHasSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_has_setting', function (Blueprint $table) {
            $table->bigInteger('tenant_id')->unsigned();
            $table->bigInteger('tenant_setting_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            // Relation defined between tenant_has_setting(tenant_id) with tenant(tenant_id)
            $table->foreign('tenant_id')->references('tenant_id')->on('tenant')->onDelete('CASCADE')->onUpdate('CASCADE');
            // Relation defined between tenant_has_setting(tenant_setting_id) with tenant_setting(tenant_setting_id)
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
        Schema::dropIfExists('tenant_has_setting');
    }
}
