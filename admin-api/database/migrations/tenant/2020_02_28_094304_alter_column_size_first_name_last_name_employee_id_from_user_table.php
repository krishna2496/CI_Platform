<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnSizeFirstNameLastNameEmployeeIdFromUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `user` CHANGE `first_name` `first_name` varchar(255)");
            \DB::statement("ALTER TABLE `user` CHANGE `last_name` `last_name` varchar(255)");
            \DB::statement("ALTER TABLE `user` CHANGE `employee_id` `employee_id` varchar(255)");
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
            \DB::statement("ALTER TABLE `user` CHANGE `first_name` `first_name` varchar(16)");
            \DB::statement("ALTER TABLE `user` CHANGE `last_name` `last_name` varchar(16)");
            \DB::statement("ALTER TABLE `user` CHANGE `employee_id` `employee_id` varchar(16)");
        });
    }
}
