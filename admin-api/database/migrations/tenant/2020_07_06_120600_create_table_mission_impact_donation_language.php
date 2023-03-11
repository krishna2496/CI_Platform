<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMissionImpactDonationLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_impact_donation_language', function (Blueprint $table) {
            $table->uuid('mission_impact_donation_language_id');
            $table->string('impact_donation_id', 36);
            $table->unsignedBigInteger('language_id');
            $table->mediumText('content', 160);
            $table->timestamps();
            $table->softDeletes();
            $table->primary('mission_impact_donation_language_id', 'pk_id');

            // Set references with mission table
            $table->foreign('impact_donation_id')
                ->references('mission_impact_donation_id')
                ->on('mission_impact_donation')
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
        Schema::dropIfExists('mission_impact_donation_language');
    }
}
