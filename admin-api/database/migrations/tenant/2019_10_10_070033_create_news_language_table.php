<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsLanguageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_language', function (Blueprint $table) {
            $table->bigIncrements('news_language_id')->unsigned();
            $table->unsignedBigInteger('news_id');
            $table->unsignedBigInteger('language_id');
            $table->string('title', 255);
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('news_id')->references('news_id')->on('news')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_language');
    }
}
