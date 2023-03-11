<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMissionLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_language', function (Blueprint $table) {
            
            $table->bigIncrements('mission_language_id')->unsigned();
            $table->unsignedBigInteger('mission_id');
            $table->integer('language_id')->length(1)->default(1);
            $table->string('title', 191);
            $table->string('short_description', 191)->nullable();
            $table->text('description'); 
            $table->text('objective')->nullable();
            $table->text('custom_information')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('mission_language');
    }
}
