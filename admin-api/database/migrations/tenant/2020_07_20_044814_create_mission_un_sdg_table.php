<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionUnSdgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_un_sdg', function (Blueprint $table) {
            $table->uuid('mission_un_sdg_id')->primary();
            $table->unsignedBigInteger('mission_id');
            $table->integer('un_sdg_number');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('mission_id')->references('mission_id')->on('mission');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_un_sdg');
    }
}
