<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnSizeIsoFromCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('country', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `country` CHANGE `ISO` `ISO` varchar(3)");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('country', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `country` CHANGE `ISO` `ISO` varchar(16)");
        });
    }
}
