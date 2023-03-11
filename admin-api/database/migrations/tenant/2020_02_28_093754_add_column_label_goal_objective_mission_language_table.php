<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLabelGoalObjectiveMissionLanguageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('mission_language', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `mission_language`  ADD `label_goal_objective` VARCHAR(255) NULL DEFAULT NULL  AFTER `custom_information`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mission_language', function (Blueprint $table) {
            $table->dropColumn('label_goal_objective');
        });
    }
}
