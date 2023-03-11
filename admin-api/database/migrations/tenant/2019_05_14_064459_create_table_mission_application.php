<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMissionApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_application', function (Blueprint $table) {
            
            $table->bigIncrements('mission_application_id')->unsigned();
            $table->unsignedBigInteger('mission_id');
            $table->unsignedBigInteger('user_id');            
            $table->dateTime('applied_at');
            $table->text('motivation');
            $table->unsignedBigInteger('availability_id');
            $table->enum('approval_status',['AUTOMATICALLY_APPROVED', 'PENDING','REFUSED']);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('mission_id')->references('mission_id')->on('mission')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('availability_id')->references('availability_id')->on('availability')->onDelete('CASCADE')->onUpdate('CASCADE');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_application');
    }
}
