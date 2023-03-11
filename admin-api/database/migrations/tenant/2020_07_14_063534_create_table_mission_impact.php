<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMissionImpact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_impact', function (Blueprint $table) {
            $table->uuid('mission_impact_id')->primary();
            $table->unsignedBigInteger('mission_id');
            $table->text('icon_path')->nullable();
            $table->integer('sort_key', false);
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
        Schema::dropIfExists('mission_impact');
    }
}
