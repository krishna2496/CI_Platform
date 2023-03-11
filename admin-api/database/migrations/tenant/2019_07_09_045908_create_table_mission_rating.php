<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMissionRating extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_rating', function (Blueprint $table) {
            $table->bigIncrements('mission_rating_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('mission_id');
            $table->float('rating');
            $table->timestamps();
            $table->softDeletes();

            // Set references with user table
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('CASCADE')->onUpdate('CASCADE');
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
        Schema::dropIfExists('mission_rating');
    }
}
