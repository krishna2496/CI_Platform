<?php

namespace App\Libraries\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedAccount;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

interface PaymentGatewayInterface
{
    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @param string
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedAccount
     */
    public function getAccount(string $accountId): PaymentGatewayDetailedAccount;

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     */
    public function createCustomer(PaymentGatewayDetailedCustomer $customer): PaymentGatewayDetailedCustomer;

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment
     */
    public function createPayment(PaymentGatewayDetailedPayment $detailedPayment): PaymentGatewayDetailedPayment;

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment
     */
    public function computeChargesAndFees(PaymentGatewayDetailedPayment $detailedPayment): PaymentGatewayDetailedPayment;

    /**
     * @param string
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod
     */
    public function getPaymentMethod(string $paymentMethodId): PaymentGatewayDetailedPaymentMethod;

    /**
     * @param string
     * @param string
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerPaymentMethods(string $customerId, string $paymentMethodType): Collection;

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod
     * @return void
     */
    public function updateCustomerPaymentMethod(PaymentGatewayDetailedPaymentMethod $paymentMethod): void;

    /**
     * @param string
     * @param string
     * @return void
     */
    public function attachCustomerPaymentMethod(string $customerId, string $paymentMethodId): void;

    /**
     * @param string
     * @return void
     */
    public function detachCustomerPaymentMethod(string $paymentMethodId): void;

    /**
     * @param Exception
     * @return Illuminate\Http\JsonResponse
     */
    public function getResponseByException(Exception $exception): JsonResponse;
}
