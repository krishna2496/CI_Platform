<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTenant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant', function (Blueprint $table) {
            $table->bigIncrements('tenant_id');
            $table->string('name',512)->comment('FQDN mapping');
            $table->bigInteger('sponsor_id')->unsigned();
            $table->enum('status',['1','0'])->default('1')->comment('0: Inactive, 1: Active');
            $table->enum('background_process_status', ['0','1','2','-1'])
            ->comment('0: Pending, 1: completed, 2: In-Progress, -1: Failed');
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
        Schema::dropIfExists('tenant');
    }
}
