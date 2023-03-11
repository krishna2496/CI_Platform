<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTenantLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_language', function (Blueprint $table) {
            $table->bigIncrements('tenant_language_id');
            $table->bigInteger('tenant_id')->unsigned();
            $table->bigInteger('language_id')->unsigned();
            $table->enum('default',['0','1'])->default('0');
            $table->timestamps();
            $table->softDeletes();

            // Relation defined between tenant_language(tenant_id) with tenant(tenant_id)
            $table->foreign('tenant_id')->references('tenant_id')->on('tenant')->onDelete('CASCADE')->onUpdate('CASCADE');

            // Relation defined between tenant_language(language_id) with language(language_id)
            $table->foreign('language_id')->references('language_id')->on('language')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_language');
    }
}
