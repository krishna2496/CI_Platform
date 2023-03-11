<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('news_id')->unsigned();
            $table->string('news_image', 255)->nullable();
            $table->enum('status', ['PUBLISHED','UNPUBLISHED'])->default('UNPUBLISHED');
            $table->string('user_name', 64)->nullable();
            $table->string('user_title', 64)->nullable();
            $table->string('user_thumbnail', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news');
    }
}
