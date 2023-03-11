<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStripeIdColumnTypesForDonationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_gateway_payment_method', function(Blueprint $table) {
            $table->string('payment_gateway_payment_method_id', 255)->change();
        });

        Schema::table('payment_gateway_customer', function(Blueprint $table) {
            $table->string('payment_gateway_customer_id', 255)->change();
        });

        Schema::table('payment', function(Blueprint $table) {
            $table->string('payment_gateway_payment_id', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_gateway_payment_method', function(Blueprint $table) {
            $table->string('payment_gateway_payment_method_id', 100)->change();
        });

        Schema::table('payment_gateway_customer', function(Blueprint $table) {
            $table->string('payment_gateway_customer_id', 100)->change();
        });

        Schema::table('payment', function(Blueprint $table) {
            $table->string('payment_gateway_payment_id', 100)->change();
        });
    }
}
