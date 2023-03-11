<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('activity_log_id');
            $table->enum('type', ['TENANT','API_USER','API_USER_KEY_RENEW','TENANT_SETTINGS','LANGUAGE','TENANT_LANGUAGE']);
            $table->enum('action', ['CREATED','UPDATED','DELETED','ENABLED','DISABLED']);
            $table->string('object_class', 500);
            $table->unsignedBigInteger('object_id')->nullable();
            $table->text('object_value')->nullable();
            $table->dateTime('date')->default(\DB::raw('CURRENT_TIMESTAMP(0)'));            
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
        Schema::dropIfExists('activity_log');
    }
}
