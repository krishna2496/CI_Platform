<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTimesheet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
    public function up()
    {
        Schema::create('timesheet', function (Blueprint $table) {
            $table->bigIncrements('timesheet_id')->unsigned();
            $table->unsignedBigInteger('user_id'); // FK users id
            $table->unsignedBigInteger('mission_id');
            $table->time('time')->nullable();
            $table->integer('action')->nullable();
            $table->date('date_volunteered');
            $table->enum('day_volunteered', ['WORKDAY','HOLIDAY','WEEKEND']);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('status_id')->default(1);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('mission_id')->references('mission_id')->on('mission')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('status_id')->references('timesheet_status_id')->on('timesheet_status')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timesheet');
    }
}
