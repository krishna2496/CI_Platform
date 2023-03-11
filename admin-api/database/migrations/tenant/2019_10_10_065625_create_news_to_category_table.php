<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsToCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_to_category', function (Blueprint $table) {
            $table->bigIncrements('news_to_category_id')->unsigned();
            $table->unsignedBigInteger('news_id');
            $table->unsignedBigInteger('news_category_id');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('news_id')->references('news_id')->on('news')->onDelete('CASCADE')->onUpdate('CASCADE');        
            $table->foreign('news_category_id')->references('news_category_id')->on('news_category')->onDelete('CASCADE')->onUpdate('CASCADE');        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_to_category');
    }
}
