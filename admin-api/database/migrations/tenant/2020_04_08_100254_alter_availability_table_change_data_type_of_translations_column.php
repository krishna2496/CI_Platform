<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAvailabilityTableChangeDataTypeOfTranslationsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('availability', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `availability` CHANGE `translations` `translations` JSON");
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('availability', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `availability` CHANGE `translations` `translations` TEXT");
       });
    }
}
