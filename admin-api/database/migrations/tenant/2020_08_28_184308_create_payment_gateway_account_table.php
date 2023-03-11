<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewayAccountTable extends Migration
{
    public function up()
    {
        Schema::create('payment_gateway_account', function (Blueprint $table) {
            $table
                ->uuid('id')
                ->primary();
            $table
                ->uuid('organization_id')
                ->index();
            $table
                ->char('payment_gateway_account_id', 255)
                ->index();
            $table
                ->tinyInteger('payment_gateway')
                ->default(
                    config('constants.payment_gateway_types.STRIPE')
                );
            $table->timestamps();
            $table->softDeletes();
            $table
                ->foreign('organization_id')
                ->references('organization_id')
                ->on('organization');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_gateway_account');
    }
}
