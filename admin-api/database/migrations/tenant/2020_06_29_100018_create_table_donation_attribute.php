<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDonationAttribute extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('donation_attribute', function (Blueprint $table) {
            $table->uuid('donation_attribute_id')->primary();
            $table->unsignedBigInteger('mission_id');
            $table->string('goal_amount_currency', 3)->nullable();
            $table->decimal('goal_amount', 16, 4)->nullable();
            $table->boolean('show_goal_amount')->default('0');
            $table->boolean('show_donation_percentage')->default('0');
            $table->boolean('show_donation_meter')->default('0');
            $table->boolean('show_donation_count')->default('0');
            $table->boolean('show_donors_count')->default('0');
            $table->boolean('disable_when_funded')->default('0');
            $table->boolean('is_disabled')->default('0');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('mission_id')->references('mission_id')->on('mission')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('donation_attribute');
    }
}
