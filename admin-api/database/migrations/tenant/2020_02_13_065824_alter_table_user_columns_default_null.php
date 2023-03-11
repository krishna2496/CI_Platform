<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUserColumnsDefaultNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('user', function (Blueprint $table) {
			\DB::statement("ALTER TABLE `user` CHANGE `first_name` `first_name` VARCHAR(16) NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `last_name` `last_name` VARCHAR(16) NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `timezone_id` `timezone_id` BIGINT(20) UNSIGNED NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `language_id` `language_id` INT(10) UNSIGNED NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `availability_id` `availability_id` BIGINT(20) UNSIGNED NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `why_i_volunteer` `why_i_volunteer` TEXT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `employee_id` `employee_id` VARCHAR(16) NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `department` `department` VARCHAR(16) NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `city_id` `city_id` BIGINT(20) UNSIGNED NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `country_id` `country_id` BIGINT(20) UNSIGNED NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `profile_text` `profile_text` TEXT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `linked_in_url` `linked_in_url` VARCHAR(255) NULL");
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
            \DB::statement("ALTER TABLE `user` CHANGE `first_name` `first_name` VARCHAR(16) NOT NULL");
            \DB::statement("ALTER TABLE `user` CHANGE `last_name` `last_name` VARCHAR(16) NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `timezone_id` `timezone_id` BIGINT(20) UNSIGNED NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `language_id` `language_id` INT(10) UNSIGNED NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `availability_id` `availability_id` BIGINT(20) UNSIGNED NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `why_i_volunteer` `why_i_volunteer` TEXT NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `employee_id` `employee_id` VARCHAR(16) NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `department` `department` VARCHAR(16) NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `city_id` `city_id` BIGINT(20) UNSIGNED NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `country_id` `country_id` BIGINT(20) UNSIGNED NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `profile_text` `profile_text` TEXT NOT NULL");
			\DB::statement("ALTER TABLE `user` CHANGE `linked_in_url` `linked_in_url` VARCHAR(255) NOT NULL");
        });
    }
}
