<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNewsCategoryTableChangeDataTypeOfTranslationsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news_category', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `news_category` CHANGE `translations` `translations` JSON");
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news_category', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `news_category` CHANGE `translations` `translations` TEXT");
        });
    }
}
