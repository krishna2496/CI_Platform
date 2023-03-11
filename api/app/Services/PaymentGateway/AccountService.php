<?php

namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway\PaymentGatewayAccount;
use App\Repositories\PaymentGateway\AccountRepository;

class AccountService
{
    /**
    * @var AccountRepository
    */
    private $accountRepository;

    /**
     * Create a new controller instance.
     *
     * @param AccountRepository $accountRepository
     *
     * @return void
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
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
        return $this->accountRepository->getByOrgId($organizationId);
    }

    /**
     * Create or update payment gateway account
     *
     * @param PaymentGatewayAccount $paymentGatewayAccount
     *
     * @return PaymentGatewayAccount
     */
    public function save(PaymentGatewayAccount $paymentGatewayAccount): PaymentGatewayAccount
    {
        return $this->accountRepository->save($paymentGatewayAccount);
    }

    /**
     * Delete payment gateway account
     *
     * @param array $filters
     *
     * @return bool
     */
    public function delete(array $filters)
    {
        return $this->accountRepository->delete($filters);
    }
}