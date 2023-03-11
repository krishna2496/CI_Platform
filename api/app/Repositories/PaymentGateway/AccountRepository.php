<?php

namespace App\Repositories\PaymentGateway;

use App\Models\PaymentGateway\PaymentGatewayAccount;

class AccountRepository
{
    /**
    * @var PaymentGatewayAccount: Model
    */
    private $paymentGatewayAccount;

    /**
     * Create a new controller instance.
     *
     * @param PaymentGatewayAccount $paymentGatewayAccount
     *
     * @return void
     */
    public function __construct(PaymentGatewayAccount $paymentGatewayAccount)
    {
        $this->paymentGatewayAccount = $paymentGatewayAccount;
    }

    /**
     * Get payment gateway account by organization ID
     *
     * @param string $organizationId
     *
     * @return PaymentGatewayAccount|null
     */
    public function getByOrgId(string $organizationId): ?PaymentGatewayAccount
    {
        return $this->paymentGatewayAccount
            ->where('organization_id', '=', $organizationId)
            ->first();
    }

    /**
     * Update Payment gateway account
     *
     * @param PaymentGatewayAccount $paymentGatewayAccount
     *
     * @return PaymentGatewayAccount
     */
    public function save(PaymentGatewayAccount $paymentGatewayAccount): PaymentGatewayAccount
    {
        $filter = [
            'organization_id' => $paymentGatewayAccount->organization_id,
            'payment_gateway_account_id' => $paymentGatewayAccount->payment_gateway_account_id,
        ];

        $this->paymentGatewayAccount->where($filter)->restore();

        $paymentGateway = $paymentGatewayAccount->payment_gateway;
        $updatedPaymentGatewayAccount = $this->paymentGatewayAccount->updateOrCreate($filter, [
            'payment_gateway' => config("constants.payment_gateway_types.$paymentGateway"),
        ]);

        $this->paymentGatewayAccount
            ->where('organization_id', '=', $updatedPaymentGatewayAccount->organization_id)
            ->where('payment_gateway_account_id', '!=', $updatedPaymentGatewayAccount->payment_gateway_account_id)
            ->delete();

        return $updatedPaymentGatewayAccount;
    }

    /**
     * Delete Payment gateway account
     *
     * @param array $filters
     *
     * @return bool
     */
    public function delete(array $filters)
    {
        return $this->paymentGatewayAccount
            ->where($filters)
            ->delete();
    }
}