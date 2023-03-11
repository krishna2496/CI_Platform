<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('message_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('sent_from')->comment('1 : User, 2 : Admin');
            $table->string('admin_name', 255)->nullable();
            $table->string('subject', 255);            
            $table->text('message');
            $table->enum('is_read', ['0', '1'])->default(0)->comment('0: Unread, 1 : Read');
            $table->enum('is_anonymous', ['0', '1'])->comment('0: Not anonymous, 1 : Anonymous');
            $table->timestamps();
            $table->softDeletes();            

            // Set references with user table
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
        Schema::dropIfExists('messages');
    }
}
