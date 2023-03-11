<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropForeignKeysTableUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('user', function (Blueprint $table) {
			$table->dropForeign('user_availability_id_foreign');
            $table->dropIndex('user_availability_id_foreign');
			
			$table->dropForeign('user_city_id_foreign');
            $table->dropIndex('user_city_id_foreign');
			
			$table->dropForeign('user_country_id_foreign');
            $table->dropIndex('user_country_id_foreign');
			
			$table->dropForeign('user_timezone_id_foreign');
            $table->dropIndex('user_timezone_id_foreign');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('user', function (Blueprint $table) {
            $table->foreign('availability_id')->references('availability_id')->on('availability')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('city_id')->references('city_id')->on('city')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('country_id')->references('country_id')->on('country')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('timezone_id')->references('timezone_id')->on('timezone')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }
}
