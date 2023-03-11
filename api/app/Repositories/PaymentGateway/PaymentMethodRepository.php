<?php

namespace App\Repositories\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Models\PaymentGateway\PaymentGatewayPaymentMethod;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class PaymentMethodRepository
{
    /**
     * @var App\Repositories\PaymentGateway\PaymentMethodRepository
     */
    private $paymentGatewayPaymentMethod;

    /**
     * @var App\Models\PaymentGateway\PaymentGatewayPaymentMethod
     * @return void
     */
    public function __construct(PaymentGatewayPaymentMethod $paymentGatewayPaymentMethod)
    {
        $this->paymentGatewayPaymentMethod = $paymentGatewayPaymentMethod;
    }

    /**
     * @param int
     * @param string|null
     * @param array|null
     * @return Illuminate\Support\Collection
     */
    public function get(
        int $userId,
        ?string $id = null,
        ?array $filters = null
    ): Collection {
        $table = $this->paymentGatewayPaymentMethod->getTable();
        $where = [
            "$table.user_id" => $userId,
            "$table.deleted_at" => null,
        ];
        if ($id) {
            $where["$table.id"] = $id;
        }

        $paymentMethods = $this->paymentGatewayPaymentMethod
            ->select("$table.*")
            ->where($where)
            ->when(isset($filters['recent']) && $filters['recent'] === true, function($query) use ($table) {
                $query->leftJoin(
                    'payment',
                    'payment.payment_gateway_payment_method_id',
                    '=',
                    "$table.id"
                )->orderBy("payment.created_at", 'DESC');
            })
            ->orderBy("$table.created_at", 'DESC')
            ->get()
            ->keyBy('id');

        $collection = Collection::make([]);
        foreach ($paymentMethods as $paymentMethod) {
            $collection->put(
                $paymentMethod->id,
                (new PaymentGatewayDetailedPaymentMethod)
                    ->setId($paymentMethod->id)
                    ->setUserId($paymentMethod->user_id)
                    ->setPaymentGatewayPaymentMethodId($paymentMethod->payment_gateway_payment_method_id)
                    ->setPaymentGatewayPaymentMethodType($paymentMethod->payment_gateway_payment_method_type)
                    ->setPaymentGateway($paymentMethod->payment_gateway)
            );
        }
        return $collection;
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod
     * @return PaymentGatewayPaymentMethod
     */
    public function create(PaymentGatewayDetailedPaymentMethod $detailedPaymentMethod): PaymentGatewayPaymentMethod
    {
        $paymentMethod = new PaymentGatewayPaymentMethod;
        $paymentMethod->user_id = $detailedPaymentMethod->getUserId();
        $paymentMethod->payment_gateway_payment_method_id = $detailedPaymentMethod->getPaymentGatewayPaymentMethodId();

        $type = $detailedPaymentMethod->getPaymentGatewayPaymentMethodType();
        $paymentMethod->payment_gateway_payment_method_type = $type ?: 'card';
        $paymentMethod->payment_gateway = $detailedPaymentMethod->getPaymentGateway();
        $paymentMethod->save();

        return $paymentMethod;
    }

    /**
     * @param int
     * @param string
     * @return void
     */
    public function delete(int $userId, string $id): void
    {
        $conditions = [
            'user_id' => $userId,
            'id' => $id,
            'deleted_at' => null,
        ];
        $paymentMethods = $this->paymentGatewayPaymentMethod
            ->where($conditions)
            ->delete();
    }
}
