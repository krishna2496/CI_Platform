<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserSkill extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('user_skill', function (Blueprint $table) {
            $table->bigIncrements('user_skill_id')->unsigned();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('skill_id');            
            $table->timestamps();
            $table->softDeletes();

            // Relation defined between user_skills(user_id) with users(id)
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('CASCADE')->onUpdate('CASCADE');
            // Relation defined between user_skills(skill_id) with skills(id)
            $table->foreign('skill_id')->references('skill_id')->on('skill')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_skill');
    }
}
