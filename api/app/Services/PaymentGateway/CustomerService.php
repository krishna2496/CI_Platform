<?php

namespace App\Services\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Repositories\PaymentGateway\CustomerRepository;
use Illuminate\Support\Collection;

class CustomerService
{
    /**
     * @var App\Repositories\PaymentGateway\CustomerRepository
     */
    private $customerRepository;

    /**
     * @param App\Repositories\PaymentGateway\CustomerRepository
     * @return void
     */
    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param int
     * @param string|null
     * @return Illuminate\Support\Collection
     */
    public function get(int $userId, ?string $id = null): Collection
    {
        $customers = $this->customerRepository->get($userId, $id);
        return $customers;
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     */
    public function create(PaymentGatewayDetailedCustomer $detailedCustomer): PaymentGatewayDetailedCustomer
    {
        return $this->customerRepository->create($detailedCustomer);
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     * @return void
     */
    public function update(PaymentGatewayDetailedCustomer $detailedCustomer): void
    {
        $this->customerRepository->update($detailedCustomer);
    }

    /**
     * @param string
     * @return void
     */
    public function delete(string $userId): void
    {
        $this->customerRepository->delete($userId, $paymentMethodId);
    }
}
