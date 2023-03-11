<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFooterPagesLanguageTableChangeDataTypeOfDescriptionColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('footer_pages_language', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `footer_pages_language` CHANGE `description` `description` JSON");
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('footer_pages_language', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `footer_pages_language` CHANGE `description` `description` TEXT");
       });
    }
}
