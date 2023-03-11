<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewayPaymentMethodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateway_payment_method', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('payment_gateway_payment_method_id', 100);
            $table->string('payment_gateway_payment_method_type', 20);
            $table->smallInteger('payment_gateway');
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('user_id')
                ->references('user_id')
                ->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_gateway_payment_method');
    }
}
