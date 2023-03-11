<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableApiUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_user', function (Blueprint $table) {
            $table->bigIncrements('api_user_id');
            $table->bigInteger('tenant_id')->unsigned();
            $table->string('api_key',64);
            $table->string('api_secret',64);
            $table->enum('status',['1','0'])->default('1')->comment('0: Inactive, 1: Active');
            $table->timestamps();
            $table->softDeletes();

            // Relation defined between api_user(tenant_id) with tenant(id)
            $table->foreign('tenant_id')->references('tenant_id')->on('tenant')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_user');
    }
}
