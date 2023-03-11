<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDonationGoal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_donation_goal', function (Blueprint $table) {
            $table->uuid('user_donation_goal_id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->decimal('donation_goal', 16, 4);
            $table->unsignedInteger('donation_goal_year');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_donation_goal');
    }
}
