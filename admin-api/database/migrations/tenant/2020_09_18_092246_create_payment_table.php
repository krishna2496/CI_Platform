<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTable extends Migration
{
    const PRECISION = 16;
    const SCALE = 4;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->smallInteger('payment_gateway');
            $table->string('payment_gateway_payment_id', 100)->index();
            $table->smallInteger('status')->default(0); // pending
            $table->smallInteger('payment_method_type');
            $table->text('payment_method_details')->nullable();
            $table->char('currency', 3);
            $table->boolean('cover_fee')->nullable();
            $table->decimal('amount', self::PRECISION, self::SCALE);
            $table->decimal('transfer_amount', self::PRECISION, self::SCALE);
            $table->char('transfer_currency', 3)->nullable();
            $table->decimal('amount_converted', self::PRECISION, self::SCALE)->nullable();
            $table->decimal('transfer_amount_converted', self::PRECISION, self::SCALE)->nullable();
            $table->decimal('transfer_exchange_rate', self::PRECISION, self::SCALE)->nullable();
            $table->decimal('payment_gateway_fee', self::PRECISION, self::SCALE)->nullable();
            $table->char('payment_gateway_account_id', 255)->comment('ID of the account where the donation was transfered');
            $table->string('billing_name', 100);
            $table->string('billing_email', 100);
            $table->string('billing_phone', 30)->nullable();
            $table->string('billing_address_line_1', 255)->nullable();
            $table->string('billing_address_line_2', 255)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 100)->nullable();
            $table->string('billing_country', 3); // country code
            $table->string('billing_postal_code', 20)->nullable();
            $table->string('ip_address', 45);
            $table->string('ip_address_country', 3)->nullable(); // country ISO
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('payment_gateway_account_id')
                ->references('payment_gateway_account_id')
                ->on('payment_gateway_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment');
    }
}
