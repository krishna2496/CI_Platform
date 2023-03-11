<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPolicyPagesLanguageTableChangeDataTypeOfDescriptionColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policy_pages_language', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `policy_pages_language` CHANGE `description` `description` JSON");
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('policy_pages_language', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `policy_pages_language` CHANGE `description` `description` TEXT");
       });
    }
}
