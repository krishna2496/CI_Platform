<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSortOrderForMissionDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission_document', function (Blueprint $table) {
            $table->integer('sort_order')->length(11)->default(0)->after('document_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mission_document', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}
