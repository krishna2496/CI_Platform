<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->bigIncrements('notification_id');
            $table->unsignedBigInteger('notification_type_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('action', ['CREATED','APPROVED','REJECTED','PUBLISHED','PENDING','DECLINED','INVITE','AUTOMATICALLY_APPROVED','SUBMIT_FOR_APPROVAL','DELETED','REFUSED','PUBLISHED_FOR_APPLYING'])
            ->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->enum('is_read',['0','1'])->default('0')->comment('0: Unread, 1: Read');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            // Set references with notification_type table
            $table->foreign('notification_type_id')->references('notification_type_id')->on('notification_type')->onDelete('CASCADE')->onUpdate('CASCADE');
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
        Schema::dropIfExists('notification');
    }
}
