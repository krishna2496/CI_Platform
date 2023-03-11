<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMissionTabLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_tab_language', function (Blueprint $table) {
            $table->uuid('mission_tab_language_id')->primary();
            $table->string('mission_tab_id', 36);
            $table->integer('language_id');
            $table->text('name');
            $table->text('section');
            $table->timestamps();
            $table->softDeletes();

            // Set references with mission table
            $table->foreign('mission_tab_id')->references('mission_tab_id')->on('mission_tab')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_tab_language');
    }
}
