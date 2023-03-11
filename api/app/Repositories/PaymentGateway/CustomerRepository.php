<?php

namespace App\Repositories\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Models\PaymentGateway\PaymentGatewayCustomer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class CustomerRepository
{
    /**
     * @var App\Repositories\PaymentGateway\PaymentMethodRepository
     */
    private $paymentGatewayCustomer;

    /**
     * @var App\Models\PaymentGateway\PaymentGatewayCustomer
     * @return void
     */
    public function __construct(PaymentGatewayCustomer $paymentGatewayCustomer)
    {
        $this->paymentGatewayCustomer = $paymentGatewayCustomer;
    }

    /**
     * @param int
     * @param string|null
     * @return Illuminate\Support\Collection
     */
    public function get(int $userId, ?string $id = null): Collection
    {
        $where = [
            'user_id' => $userId,
            'deleted_at' => null,
        ];
        if ($id) {
            $where['id'] = $id;
        }
        $customers = $this->paymentGatewayCustomer->where($where)->get()->keyBy('id');
        if (!$customers->count()) {
            throw new ModelNotFoundException;
        }
        $collection = Collection::make([]);
        foreach ($customers as $customer) {
            $collection->put(
                $customer->id,
                (new PaymentGatewayDetailedCustomer)
                    ->setId($customer->id)
                    ->setUserId($customer->user_id)
                    ->setPaymentGatewayCustomerId($customer->payment_gateway_customer_id)
                    ->setPaymentGateway($customer->payment_gateway)
            );
        }
        return $collection;
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     * @return App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     */
    public function create(PaymentGatewayDetailedCustomer $detailedCustomer): PaymentGatewayDetailedCustomer
    {
        $customer = new PaymentGatewayCustomer;
        $customer->user_id = $detailedCustomer->getUserId();
        $customer->payment_gateway_customer_id = $detailedCustomer->getPaymentGatewayCustomerId();
        $customer->payment_gateway = $detailedCustomer->getPaymentGateway();
        $customer->user_id = $detailedCustomer->getUserId();
        $customer->save();
        return $this->get($detailedCustomer->getUserId())->first();
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     * @return void
     */
    public function update(PaymentGatewayDetailedCustomer $detailedCustomer): void
    {
    }

    /**
     * @param int
     * @return void
     */
    public function delete(int $userId): void
    {
    }
}
