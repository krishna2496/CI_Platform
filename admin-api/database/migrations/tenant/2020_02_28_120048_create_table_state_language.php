<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableStateLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('state_language', function (Blueprint $table) {
            $table->bigIncrements('state_language_id');
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('language_id');
            $table->string('name', 255);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('state_id')->references('state_id')->on('state')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('state_language');
    }
}
