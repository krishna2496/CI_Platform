<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoryMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('story_media', function (Blueprint $table) {
            $table->bigIncrements('story_media_id')->unsigned();
            $table->unsignedBigInteger('story_id');
            $table->string('type', 8);
            $table->text('path');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('story_id')->references('story_id')->on('story')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('story_media');
    }
}
