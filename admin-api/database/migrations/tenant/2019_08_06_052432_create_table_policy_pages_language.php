<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePolicyPagesLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
    public function up()
    {
        Schema::create('policy_pages_language', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->unsignedBigInteger('page_id');
            $table->integer('language_id');
            $table->string('title', 255);
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('page_id')->references('page_id')->on('policy_page')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('policy_pages_language');
    }
}
