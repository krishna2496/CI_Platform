<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMissionImpactLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_impact_language', function (Blueprint $table) {
            $table->uuid('mission_impact_language_id')->primary();
            $table->string('mission_impact_id', 36);
            $table->integer('language_id', false);
            $table->text('content', 300);
            $table->timestamps();
            $table->softDeletes();

            // Set references with mission impact table
            $table->foreign('mission_impact_id')->references('mission_impact_id')->on('mission_impact')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_impact_language');
    }
}
