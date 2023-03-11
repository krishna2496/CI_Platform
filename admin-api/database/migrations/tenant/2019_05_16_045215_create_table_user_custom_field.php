<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserCustomField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('user_custom_field', function (Blueprint $table) {
            $table->bigIncrements('field_id')->unsigned();
            $table->text('name');
            $table->enum('type', ['text','email','drop-down','radio','checkbox','multiselect','textarea']);
            $table->text('translations');
            $table->integer('is_mandatory')->length(1)->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_custom_field');
    }
}
