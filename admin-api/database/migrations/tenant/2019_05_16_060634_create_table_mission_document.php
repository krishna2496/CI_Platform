<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMissionDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_document', function (Blueprint $table) {
            $table->bigIncrements('mission_document_id')->unsinged();
            $table->unsignedBigInteger('mission_id');
            $table->string('document_name',255);
            $table->string('document_type',255);
            $table->string('document_path',255);
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
        Schema::dropIfExists('mission_document');
    }
}
