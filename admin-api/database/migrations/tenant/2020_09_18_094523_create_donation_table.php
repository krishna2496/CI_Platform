<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('donation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('mission_id')->index();
            $table->uuid('payment_id')->index();
            $table->uuid('organization_id');
            $table->bigInteger('user_id')->nullable();
            $table->boolean('is_archived')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('mission_id')
                ->references('mission_id')
                ->on('mission');
            $table
                ->foreign('organization_id')
                ->references('organization_id')
                ->on('organization');
            $table
                ->foreign('payment_id')
                ->references('id')
                ->on('payment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('donation');
    }
}
