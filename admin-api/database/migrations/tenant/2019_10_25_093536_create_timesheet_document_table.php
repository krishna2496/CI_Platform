<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimesheetDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timesheet_document', function (Blueprint $table) {
            $table->bigIncrements('timesheet_document_id');
            $table->unsignedBigInteger('timesheet_id');
            $table->string('document_name', 255);
            $table->string('document_type', 255);
            $table->string('document_path', 255);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('timesheet_id')->references('timesheet_id')->on('timesheet')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timesheet_document');
    }
}
