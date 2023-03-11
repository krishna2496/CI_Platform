<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserCustomFieldValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            
        Schema::create('user_custom_field_value', function (Blueprint $table) {
            $table->bigIncrements('user_custom_field_value_id')->unsigned();
            $table->unsignedBigInteger('field_id'); // FK user custom fields id
            $table->unsignedBigInteger('user_id'); // FK users id
            $table->text('value');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('field_id')->references('field_id')->on('user_custom_field')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_custom_field_value');
    }
}
