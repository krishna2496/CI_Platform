<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMissionMedia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_media', function (Blueprint $table) {
            
            $table->bigIncrements('mission_media_id')->unsinged();
            $table->unsignedBigInteger('mission_id');
            $table->string('media_name',64);
            $table->string('media_type',4);
            $table->string('media_path',255);
            $table->enum('status', ['0', '1'])->default(1);
            $table->enum('default', ['0', '1'])->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('mission_id')->references('mission_id')->on('mission')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_media');
    }
}
