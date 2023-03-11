<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropVolunteeringAttributeColumnsFromMissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission', function (Blueprint $table) {
            $table->dropForeign('mission_availability_id_foreign');
            $table->dropIndex('mission_availability_id_foreign');
            $table->dropColumn('availability_id');
            $table->dropColumn('total_seats');
            $table->dropColumn('is_virtual');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mission', function (Blueprint $table) {
            $table->unsignedBigInteger('availability_id')->after('publication_status')->nullable();
            $table->integer('total_seats')->nullable()->after('end_date');
            $table->enum('is_virtual', ['0', '1'])->default('0')->after('organisation_detail');
            $table->foreign('availability_id')->references('availability_id')->on('availability')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }
}
