<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCityLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city_language', function (Blueprint $table) {
            $table->bigIncrements('city_language_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('language_id');
            $table->string('name', 255);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('city_id')->references('city_id')->on('city')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('city_language');
    }
}
