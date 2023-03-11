<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoryInviteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('story_invite', function (Blueprint $table) {
            $table->bigIncrements('story_invite_id');
            $table->unsignedBigInteger('story_id');
            $table->unsignedBigInteger('from_user_id');
            $table->unsignedBigInteger('to_user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('story_id')->references('story_id')->on('story')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('from_user_id')->references('user_id')->on('user')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('to_user_id')->references('user_id')->on('user')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('story_invite');
    }
}
