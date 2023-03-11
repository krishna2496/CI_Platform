<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMissionDonationImpact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_impact_donation', function (Blueprint $table) {
            $table->uuid('mission_impact_donation_id')->primary();
            $table->unsignedBigInteger('mission_id');
            $table->decimal('amount', 16, 4);
            $table->timestamps();
            $table->softDeletes();

            // Set references with mission table
            $table->foreign('mission_id')
                ->references('mission_id')
                ->on('mission')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_impact_donation');
    }
}
