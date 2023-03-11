<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMission extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('mission', function (Blueprint $table) {
            $table->bigIncrements('mission_id')->unsigned();
            $table->unsignedBigInteger('theme_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('country_id');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('total_seats')->nullable();
            $table->enum('mission_type', ['TIME','GOAL']);
            $table->enum('publication_status', ['DRAFT','PENDING_APPROVAL','REFUSED','APPROVED','PUBLISHED_FOR_VOTING','PUBLISHED_FOR_APPLYING','UNPUBLISHED']);
            $table->unsignedBigInteger('availability_id');
            $table->bigInteger('organisation_id')->unsigned();
            $table->string('organisation_name',255);
            $table->text('organisation_detail')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Relation defined between missions(city_id) with cities(id)
            $table->foreign('city_id')->references('city_id')->on('city')->onDelete('CASCADE')->onUpdate('CASCADE');
            // Relation defined between missions(country_id) with counties(id)
            $table->foreign('country_id')->references('country_id')->on('country')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('theme_id')->references('mission_theme_id')->on('mission_theme')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('availability_id')->references('availability_id')->on('availability')->onDelete('CASCADE')->onUpdate('CASCADE');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('mission');
    }

}
