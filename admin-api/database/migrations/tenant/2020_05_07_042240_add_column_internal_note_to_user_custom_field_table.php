<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInternalNoteToUserCustomFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_custom_field', function (Blueprint $table) {
            $table->text('internal_note')
                ->nullable()
                ->default(null)
                ->after('is_mandatory');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_custom_field', function (Blueprint $table) {
            $table->dropColumn('internal_note');
        });
    }
}
