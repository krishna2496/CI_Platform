<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTenantCurrency extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_currency', function (Blueprint $table) {
            $table->string('code', 3);
            $table->unsignedBigInteger('tenant_id');
            $table->boolean('default')->default(0);
            $table->boolean('is_active')->default(0)->comment('0: Inactive, 1: Active');
            $table->timestamps();
            $table->primary(['code', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_currency');
    }
}
