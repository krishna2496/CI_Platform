<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentFailureTable extends Migration
{
    const PAYMENT_FAILURE_TABLE = 'payment_failure';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable(self::PAYMENT_FAILURE_TABLE)) {
            Schema::create(self::PAYMENT_FAILURE_TABLE, function (Blueprint $table) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_bin';
                $table->string('payment_gateway_payment_id', 255)->index();
                $table->json('failure_data');
                $table->timestamp('received_at')->useCurrent()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::PAYMENT_FAILURE_TABLE);
    }
}
