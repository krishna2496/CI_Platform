<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVolunteeringAttribute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('volunteering_attribute', function (Blueprint $table) {
            $table->uuid('volunteering_attribute_id')->primary();
            $table->unsignedBigInteger('mission_id');
            $table->unsignedBigInteger('availability_id');
            $table->integer('total_seats')->nullable();
            $table->boolean('is_virtual')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Set references with mission table
            $table->foreign('mission_id')
                ->references('mission_id')
                ->on('mission')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('availability_id')
                ->references('availability_id')
                ->on('availability')
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
        Schema::dropIfExists('volunteering_attribute');
    }
}
