<?php

namespace App\Libraries\PaymentGateway\Stripe;

use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Helpers\ResponseHelper;
use App\Libraries\Amount;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedAccount;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedTransaction;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedTransfer;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGatewayInterface
{
    const PAYMENT_GATEWAY = 'STRIPE';
    const ZERO_DECIMAL_CURRENCIES = [
        'BIF',
        'CLP',
        'DJF',
        'GNF',
        'JPY',
        'KMF',
        'KRW',
        'MGA',
        'PYG',
        'RWF',
        'VND',
        'VUV',
        'XAF',
        'XOF',
        'XPF'
    ];
    const DONATION_FEE = 0.05;
    const DECIMAL_PRECISION = 4;
    const DONATION_FIXED_FEES = [
        'EUR' => 0.25,
        'USD' => 0.30,
        'CAD' => 0.30,
        'GBP' => 0.20,
        'CHF' => 0.30
    ];

    /**
     * @var Stripe\StripeClient
     */
    private $stripeClient;

    /**
     * @return int
     */
    public function getType(): int
    {
        return config('constants.payment_gateway_types.STRIPE');
    }

    /**
     * @return Stripe\StripeClient
     */
    protected function getClient(): StripeClient
    {
        if (!$this->stripeClient) {
            $this->stripeClient = new StripeClient(env('STRIPE_SECRET_API_KEY'));
        }
        return $this->stripeClient;
    }

    /**
     * @param string
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedAccount
     */
    public function getAccount(string $accountId): PaymentGatewayDetailedAccount
    {
        try {
            $account = $this->getClient()->accounts->retrieve($accountId);
        } catch (Exception $e) {
            // catch as a generic exception, rethrow as a chained exception.
            list($code, $message) = $this->classifyException($e);
            throw (new PaymentGatewayException($message, $code, $e))
                ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        }
        $detailedAccount = new PaymentGatewayDetailedAccount;
        $detailedAccount
            ->setPaymentGatewayAccountId($account->id)
            ->setName($account->business_profile->name)
            ->setEmail($account->email)
            ->setCountry($account->country)
            ->setDefaultCurrency($account->default_currency)
            ->setPayoutsEnabled($account->payouts_enabled);
        return $detailedAccount;
    }

    /**
     * @param string
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     */
    public function getCustomer(string $customerId): PaymentGatewayDetailedCustomer
    {
        try {
            $customer = $this->getClient()->customers->retrieve($customerId);
        } catch (Exception $e) {
            // catch as a generic exception, rethrow as a chained exception.
            list($code, $message) = $this->classifyException($e);
            throw (new PaymentGatewayException($message, $code, $e))
                ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        }
        $detailedCustomer = (new PaymentGatewayDetailedCustomer)
            ->setPaymentGatewayCustomerId($customer->id)
            ->setName($customer->name)
            ->setEmail($customer->email)
            ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        return $detailedCustomer;
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     */
    public function createCustomer(PaymentGatewayDetailedCustomer $detailedCustomer): PaymentGatewayDetailedCustomer
    {
        try {
            $customerData = [
                'description' => sprintf('Customer for User %d', $detailedCustomer->getUserId()),
            ];
            !empty($detailedCustomer->getName())  && $customerData['name'] = $detailedCustomer->getName();
            !empty($detailedCustomer->getEmail()) && $customerData['email'] = $detailedCustomer->getEmail();
            $customer = $this->getClient()->customers->create($customerData);
        } catch (Exception $e) {
            // catch as a generic exception, rethrow as a chained exception.
            list($code, $message) = $this->classifyException($e);
            throw (new PaymentGatewayException($message, $code, $e))
                ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        }
        $detailedCustomer->setPaymentGatewayCustomerId($customer->id);
        return $detailedCustomer;
    }

    /**
     * @param string
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod
     */
    public function getPaymentMethod(string $paymentMethodId): PaymentGatewayDetailedPaymentMethod
    {
        try {
            $paymentMethod = $this->getClient()->paymentMethods->retrieve($paymentMethodId);
        } catch (Exception $e) {
            // catch as a generic exception, rethrow as a chained exception.
            list($code, $message) = $this->classifyException($e);
            throw (new PaymentGatewayException($message, $code, $e))
                ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        }
        $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
            ->setPaymentGatewayPaymentMethodId($paymentMethod->id)
            ->setPaymentGatewayPaymentMethodType($paymentMethod->type)
            ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'))
            ->setAddressLine1($paymentMethod->billing_details->address->line1)
            ->setAddressLine2($paymentMethod->billing_details->address->line2)
            ->setCity($paymentMethod->billing_details->address->city)
            ->setState($paymentMethod->billing_details->address->state)
            ->setPostalCode($paymentMethod->billing_details->address->postal_code)
            ->setCountry($paymentMethod->billing_details->address->country)
            ->setDetails([
                'name' => $paymentMethod->billing_details->name,
                'email' => $paymentMethod->billing_details->email,
                'phone' => $paymentMethod->billing_details->phone,
                'card_brand' => $paymentMethod->card->brand,
                'last_4_digits' => $paymentMethod->card->last4,
                'expire_month' => $paymentMethod->card->exp_month,
                'expire_year' => $paymentMethod->card->exp_year,
            ]);
        return $detailedPaymentMethod;
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment
     *
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment
     */
    public function createPayment(PaymentGatewayDetailedPayment $detailedPayment): PaymentGatewayDetailedPayment
    {
        $payment = $detailedPayment->getPayment();
        $paymentMethodType = array_search(
            $payment->payment_method_type,
            config('constants.payment_method_types')
        );
        $convertedAmounts = $this->zeroDecimalConversion([
            'charge' => $payment->amount->getValue(null, true),
            'transfer' => $payment->transfer_amount->getValue(null, true)
        ], $payment->currency);

        try {
            $paymentData = [
                'payment_method_types' => [strtolower($paymentMethodType)],
                'amount' => intval($convertedAmounts['charge']),
                'currency' => strtolower($payment->currency),
                'receipt_email' => $payment->billing_email,
                'description' => 'Donation by User ' . $detailedPayment->getUserId() . ' to Mission ' .$detailedPayment->getMissionId(),
                'on_behalf_of' => $detailedPayment->getConnectedAccountId(),
                'customer' => $detailedPayment->getCustomerId(),
                'transfer_data' => [
                    'amount' => intval($convertedAmounts['transfer']),
                    'destination' => $detailedPayment->getConnectedAccountId()
                ],
                'payment_method_options' => [
                    'card' => [
                        'request_three_d_secure' => 'any'
                    ]
                ],
                'metadata' => [
                    'mission_id' => $detailedPayment->getMissionId(),
                    'organization_id' => $detailedPayment->getOrganizationId(),
                    'tenant_id' => $detailedPayment->getTenantId()
                ]
            ];

            if ($detailedPayment->getPaymentMethodId()) {
                $paymentData['payment_method'] = $detailedPayment->getPaymentMethodId();
            }

            $paymentIntent = $this->getClient()->paymentIntents->create($paymentData);
        } catch (Exception $e) {
            throw new PaymentGatewayException($e->getMessage(), $e->getCode());
        }

        $detailedPayment
            ->setPaymentGatewayPaymentId($paymentIntent->id)
            ->setClientSecret($paymentIntent->client_secret);

        return $detailedPayment;
    }

    /**
     * Computes the amount to charge and to transfer to the connected account
     *
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment
     */
    public function computeChargesAndFees(PaymentGatewayDetailedPayment $detailedPayment): PaymentGatewayDetailedPayment
    {
        $payment = $detailedPayment->getPayment();
        $currency = $payment->currency;
        $coverFee = $payment->cover_fee;
        $hundredPercentSetting = $detailedPayment->getHundredPercentSetting();

        $amount = $detailedPayment->getAmountDonated();
        $chargeAmount = $amount;
        $transferAmount = $amount;

        if (!$hundredPercentSetting && $coverFee) {
            // The user chooses to shoulder the service fee, so we add it to the charge
            $chargeAmount = new Amount(bcdiv(
                $amount->add(self::DONATION_FIXED_FEES[$currency])->getValue(),
                bcsub(1, self::DONATION_FEE, self::DECIMAL_PRECISION),
                self::DECIMAL_PRECISION
            ));
        } else if (!$hundredPercentSetting && !$coverFee) {
            // Neither user or client will shoulder the fees
            // so we deduct the service fee from customer's donation amount
            $transferAmount = new Amount($amount->subtract(
                bcadd(
                    $amount->multiply(self::DONATION_FEE)->getValue(),
                    self::DONATION_FIXED_FEES[$currency],
                    self::DECIMAL_PRECISION
                ))
            );
        }

        $payment->setAttribute('amount', $chargeAmount->getValue(null, true));
        $payment->setAttribute('transfer_amount', $transferAmount->getValue(null, true));

        $detailedPayment->setPayment($payment);

        return $detailedPayment;
    }

    /**
     * Stripe specific requirement to convert amount to its currency's smallest unit
     *
     * @param array $amounts
     * @param string $currency
     *
     * @return array $converted
     */
    private function zeroDecimalConversion(array $amounts, string $currency): array
    {
        $converted = [];
        $isCurrencyZeroDecimal = in_array(
            strtoupper($currency),
            self::ZERO_DECIMAL_CURRENCIES
        );

        foreach ($amounts as $key => $amount) {
            if ($isCurrencyZeroDecimal) {
                $converted[$key] = bcadd($amount, '0', 0);
                continue;
            }
            $converted[$key] = bcmul($amount, '100', 0);
        }

        return $converted;
    }

    /**
     * @param string
     * @param string
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerPaymentMethods(string $customerId, string $paymentMethodType): Collection
    {
        try {
            $filter = [
                'customer' => $customerId,
                'type' => $paymentMethodType,
            ];
            $paymentMethods = $this->getClient()->paymentMethods->all($filter);
        } catch (Exception $e) {
            // catch as a generic exception, rethrow as a chained exception.
            list($code, $message) = $this->classifyException($e);
            throw (new PaymentGatewayException($message, $code, $e))
                ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        }
        $collection = Collection::make([]);
        foreach ($paymentMethods->getIterator() as $paymentMethod) {
            $collection->put(
                $paymentMethod->id,
                (new PaymentGatewayDetailedPaymentMethod)
                    ->setPaymentGatewayPaymentMethodId($paymentMethod->id)
                    ->setPaymentGatewayPaymentMethodType($paymentMethod->type)
                    ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'))
                    ->setAddressLine1($paymentMethod->billing_details->address->line1)
                    ->setAddressLine2($paymentMethod->billing_details->address->line2)
                    ->setCity($paymentMethod->billing_details->address->city)
                    ->setState($paymentMethod->billing_details->address->state)
                    ->setPostalCode($paymentMethod->billing_details->address->postal_code)
                    ->setCountry($paymentMethod->billing_details->address->country)
                    ->setDetails([
                        'name' => $paymentMethod->billing_details->name,
                        'email' => $paymentMethod->billing_details->email,
                        'phone' => $paymentMethod->billing_details->phone,
                        'card_brand' => $paymentMethod->card->brand,
                        'last_4_digits' => $paymentMethod->card->last4,
                        'expire_month' => $paymentMethod->card->exp_month,
                        'expire_year' => $paymentMethod->card->exp_year,
                    ])
            );
        }
        return $collection;
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod
     * @return void
     */
    public function updateCustomerPaymentMethod(PaymentGatewayDetailedPaymentMethod $detailedPaymentMethod): void
    {
        try {
            $paymentMethodId = $detailedPaymentMethod->getPaymentGatewayPaymentMethodId();
            $cardDetails = $detailedPaymentMethod->getDetails();
            $updateData = array_filter([
                'card' => array_filter([
                    'exp_month' => $cardDetails['expire_month'] ?? null,
                    'exp_year' => $cardDetails['expire_year'] ?? null,
                ]),
                'billing_details' => array_filter([
                    'name' => $cardDetails['name'] ?? null,
                    'email' => $cardDetails['email'] ?? null,
                    'phone' => $cardDetails['phone'] ?? null,
                    'address' => array_filter([
                        'line1' => $detailedPaymentMethod->getAddressLine1(),
                        'line2' => $detailedPaymentMethod->getAddressLine2(),
                        'city' => $detailedPaymentMethod->getCity(),
                        'state' => $detailedPaymentMethod->getState(),
                        'postal_code' => $detailedPaymentMethod->getPostalCode(),
                        'country' => $detailedPaymentMethod->getCountry(),
                    ]),
                ]),
            ]);
            $paymentMethod = $this->getClient()->paymentMethods->update($paymentMethodId, $updateData);
        } catch (Exception $e) {
            // catch as a generic exception, rethrow as a chained exception.
            list($code, $message) = $this->classifyException($e);
            throw (new PaymentGatewayException($message, $code, $e))
                ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        }
    }

    /**
     * @param string
     * @param string
     * @return void
     */
    public function attachCustomerPaymentMethod(string $customerId, string $paymentMethodId): void
    {
        try {
            $customer = ['customer' => $customerId];
            $paymentMethod = $this->getClient()->paymentMethods->attach($paymentMethodId, $customer);
        } catch (Exception $e) {
            // catch as a generic exception, rethrow as a chained exception.
            list($code, $message) = $this->classifyException($e);
            throw (new PaymentGatewayException($message, $code, $e))
                ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        }
    }

    /**
     * @param string
     * @return void
     */
    public function detachCustomerPaymentMethod(string $paymentMethodId): void
    {
        try {
            $paymentMethod = $this->getClient()->paymentMethods->detach($paymentMethodId);
        } catch (Exception $e) {
            // catch as a generic exception, rethrow as a chained exception.
            list($code, $message) = $this->classifyException($e);
            throw (new PaymentGatewayException($message, $code, $e))
                ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        }
    }

    /**
     * @param string $transactionId
     * @return PaymentGatewayDetailedTransaction
     */
    public function getTransaction($transactionId): PaymentGatewayDetailedTransaction
    {
        $detailedTransaction = new PaymentGatewayDetailedTransaction;
        $transaction = $this->getClient()
            ->balanceTransactions
            ->retrieve($transactionId);

        $detailedTransaction
            ->setCurrency($transaction->currency)
            ->setAmount($transaction->amount)
            ->setExchangeRate($transaction->exchange_rate)
            ->setFee($transaction->fee);

        return $detailedTransaction;
    }

    /**
     * @param string $transferId
     * @return PaymentGatewayDetailedTransfer
     */
    public function getTransfer($transferId): PaymentGatewayDetailedTransfer
    {
        $detailedTransfer = new PaymentGatewayDetailedTransfer;
        $transfer = $this->getClient()
            ->transfers
            ->retrieve($transferId);

        $detailedTransfer->setAmount($transfer->amount);

        return $detailedTransfer;
    }

    /**
     * @param Exception
     * @return Illuminate\Http\JsonResponse
     */
    public function getResponseByException(Exception $exception): JsonResponse
    {
        try {
            if (!$exception instanceof PaymentGatewayException) {
                throw new InvalidArgumentException;
            }
            $chainedException = $exception->getChainedException();
            if (!$chainedException || !method_exists($chainedException, 'getError')) {
                throw new InvalidArgumentException;
            }
            $externalError = $chainedException->getError();
            return (new ResponseHelper)->error(
                $chainedException->getHttpStatus(),
                Response::$statusTexts[$chainedException->getHttpStatus()],
                $exception->getCode(),
                $exception->getMessage(),
                $externalError->code ?: null,
                sprintf('STRIPE: %s » %s',
                    $externalError->message,
                    $externalError->param
                )
            );
        } catch (Exception $e) {
            // fallback in case this method was erroneously
            // called and the exception argument does not carry
            // the required chained exception and error objects.
            return (new ResponseHelper)->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                $exception->getCode(),
                $exception->getMessage()
            );
        }
    }

    /**
     * @param Exception
     * @return array
     */
    protected function classifyException(Exception $exception): array
    {
        if ($exception instanceof CardException) {
            return [
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_CARD_DECLINED'),
                trans('messages.custom_error_message.MESSAGE_PAYMENT_GATEWAY_CARD_DECLINED'),
            ];
        } elseif ($exception instanceof InvalidRequestException) {
            return [
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_INVALID_REQUEST'),
                trans('messages.custom_error_message.MESSAGE_PAYMENT_GATEWAY_INVALID_REQUEST'),
            ];
        } elseif ($exception instanceof AuthenticationException) {
            return [
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_UNAUTHORIZED'),
                trans('messages.custom_error_message.MESSAGE_PAYMENT_GATEWAY_UNAUTHORIZED'),
            ];
        } elseif ($exception instanceof RateLimitException) {
            return [
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_RATE_LIMITED'),
                trans('messages.custom_error_message.MESSAGE_PAYMENT_GATEWAY_RATE_LIMITED'),
            ];
        } elseif ($exception instanceof ApiErrorException) {
            return [
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_INTERNAL_FAILURE'),
                trans('messages.custom_error_message.MESSAGE_PAYMENT_GATEWAY_INTERNAL_FAILURE'),
            ];
        } else {
            // exception is not from the payment gateway.
            return [
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_UNKNOWN_FAILURE'),
                trans('messages.custom_error_message.MESSAGE_PAYMENT_GATEWAY_UNKNOWN_FAILURE'),
            ];
        }
    }
}
