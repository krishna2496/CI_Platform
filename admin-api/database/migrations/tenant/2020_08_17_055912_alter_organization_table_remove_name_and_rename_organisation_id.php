<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrganizationTableRemoveNameAndRenameOrganisationId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mission', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'organization_id');
            $table->dropColumn('organisation_name');
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
            $table->renameColumn('organization_id', 'organisation_id');
            $table->string('organisation_name', 255)
                ->after('organization_id');
        });
    }
}
