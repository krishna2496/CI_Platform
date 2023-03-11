<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMissionTab extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_tab', function (Blueprint $table) {
            $table->uuid('mission_tab_id')->primary();
            $table->unsignedBigInteger('mission_id');
            $table->integer('sort_key');
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
        Schema::dropIfExists('mission_tab');
    }
}
