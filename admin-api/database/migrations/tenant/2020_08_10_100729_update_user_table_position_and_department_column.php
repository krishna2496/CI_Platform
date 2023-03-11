<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserTablePositionAndDepartmentColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            DB::statement('ALTER TABLE `user`
                MODIFY `position` VARCHAR(255),
                MODIFY `department` VARCHAR(255)'
            );
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
            DB::statement('ALTER TABLE `user`
                MODIFY `position` VARCHAR(191),
                MODIFY `department` VARCHAR(16)'
            );
        });
    }
}
