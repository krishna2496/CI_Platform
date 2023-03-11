<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization', function (Blueprint $table) {
            $table->uuid('organization_id')->primary();
            $table->string('name', 255);
            $table->string('legal_number', 255)->nullable();
            $table->string('phone_number', 120)->nullable();
            $table->string('address_line_1', 255)->nullable();
            $table->string('address_line_2', 255)->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('postal_code', 120)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('city_id')->references('city_id')->on('city')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('country_id')->references('country_id')->on('country')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization');
    }
}
