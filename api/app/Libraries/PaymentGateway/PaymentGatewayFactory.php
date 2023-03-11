<?php

namespace App\Libraries\PaymentGateway;

use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use App\Libraries\PaymentGateway\Stripe\StripePaymentGateway;

class PaymentGatewayFactory
{
    /**
     * @param int
     * @return ?string
     */
    public function getNameByType(int $type): ?string
    {
        $paymentGatewayName = array_search($type, config('constants.payment_gateway_types'));
        if ($paymentGatewayName === false) {
            throw new PaymentGatewayException("Invalid payment gateway type. [$type]");
        }
        return $paymentGatewayName;
    }

    /**
     * @param int|null
     * @return App\Libraries\PaymentGateway\PaymentGatewayInterface
     */
    public function getPaymentGateway(?int $type = null): PaymentGatewayInterface
    {
        switch ($type) {
            case null:  // default payment gateway for this factory
            case config('constants.payment_gateway_types.STRIPE'):
                return new StripePaymentGateway;
                break;

            default:
                throw new PaymentGatewayException("Invalid payment gateway type. [$type]");
                break;
        }
    }
}
