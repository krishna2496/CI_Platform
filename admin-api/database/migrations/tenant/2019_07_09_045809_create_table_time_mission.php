<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTimeMission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_mission', function (Blueprint $table) {
            $table->bigIncrements('time_mission_id');
            $table->unsignedBigInteger('mission_id');
            $table->timestamp('application_deadline')->nullable();
            $table->timestamp('application_start_date')->nullable();
            $table->timestamp('application_end_date')->nullable();
            $table->timestamp('application_start_time')->nullable();
            $table->timestamp('application_end_time')->nullable();            
            $table->timestamps();
            $table->softDeletes();

            // Set references with mission table
            $table->foreign('mission_id')->references('mission_id')->on('mission')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_mission');
    }
}
