<?php

namespace App\Services\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use App\Models\PaymentGateway\PaymentGatewayPaymentMethod;
use App\Repositories\PaymentGateway\PaymentMethodRepository;
use App\Services\PaymentGateway\CustomerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class PaymentMethodService
{
    /**
     * @var App\Repositories\PaymentGateway\PaymentMethodRepository
     */
    private $paymentMethodRepository;

    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayInterface
     */
    private $paymentGateway;

    /**
     * @var App\Services\PaymentGateway\CustomerService
     */
    private $customerService;

    /**
     * @param App\Repositories\PaymentGateway\PaymentMethodRepository
     * @param App\Libraries\PaymentGateway\PaymentGatewayFactory
     * @return void
     */
    public function __construct(
        PaymentMethodRepository $paymentMethodRepository,
        PaymentGatewayFactory $paymentGatewayFactory,
        CustomerService $customerService
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentGateway = $paymentGatewayFactory->getPaymentGateway();  // default is STRIPE
        $this->customerService = $customerService;
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
        $customers = $this->customerService->get($userId);
        $customerId = $customers->first()->getPaymentGatewayCustomerId();

        $paymentMethods = $this->paymentMethodRepository->get(
            $userId,
            $id,
            $filters
        );

        $collection = Collection::make([]);

        if ($id) {
            // if requested with an id then it will always be a single object
            $paymentMethod = $paymentMethods->first();

            if (!$paymentMethod) {
                throw new ModelNotFoundException();
            }

            $paymentMethodId = $paymentMethod->getPaymentGatewayPaymentMethodId();
            $externalPaymentMethod = $this->paymentGateway->getPaymentMethod($paymentMethodId);
            $collection->push(
                $paymentMethod
                    ->setAddressLine1($externalPaymentMethod->getAddressLine1())
                    ->setAddressLine2($externalPaymentMethod->getAddressLine2())
                    ->setCity($externalPaymentMethod->getCity())
                    ->setState($externalPaymentMethod->getState())
                    ->setPostalCode($externalPaymentMethod->getPostalCode())
                    ->setCountry($externalPaymentMethod->getCountry())
                    ->setDetails($externalPaymentMethod->getDetails())
            );
        } else {
            // only 'card' payment method types are retrieved.
            // while other types will have empty payment method details.
            $paymentMethodType = 'card';
            $externalPaymentMethods = $this->paymentGateway
                ->getCustomerPaymentMethods($customerId, $paymentMethodType);
            // populate local records with payment gateway specific details.
            foreach ($paymentMethods as $paymentMethod) {
                $paymentMethodId = $paymentMethod->getPaymentGatewayPaymentMethodId();
                if ($externalPaymentMethods->has($paymentMethodId)) {
                    $externalPaymentMethod = $externalPaymentMethods->get($paymentMethodId);
                    $paymentMethod
                        ->setAddressLine1($externalPaymentMethod->getAddressLine1())
                        ->setAddressLine2($externalPaymentMethod->getAddressLine2())
                        ->setCity($externalPaymentMethod->getCity())
                        ->setState($externalPaymentMethod->getState())
                        ->setPostalCode($externalPaymentMethod->getPostalCode())
                        ->setCountry($externalPaymentMethod->getCountry())
                        ->setDetails($externalPaymentMethod->getDetails());
                }
                $collection->push($paymentMethod);
            }
        }
        return $collection;
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod
     * @return PaymentGatewayPaymentMethod
     */
    public function create(PaymentGatewayDetailedPaymentMethod $detailedPaymentMethod): PaymentGatewayPaymentMethod
    {
        return $this->paymentMethodRepository->create($detailedPaymentMethod);
    }

    /**
     * @param App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod
     * @return void
     */
    public function update(PaymentGatewayDetailedPaymentMethod $detailedPaymentMethod): void
    {
        $paymentMethods = $this->paymentMethodRepository->get(
            $detailedPaymentMethod->getUserId(),
            $detailedPaymentMethod->getId()
        );
        // inject the payment method id to the DTO
        $paymentMethod = $paymentMethods->first();
        if (!$paymentMethod) {
            throw new ModelNotFoundException();
        }
        $detailedPaymentMethod
            ->setPaymentGatewayPaymentMethodId($paymentMethod->getPaymentGatewayPaymentMethodId())
            ->setPaymentGatewayPaymentMethodType($paymentMethod->getPaymentGatewayPaymentMethodType())
            ->setPaymentGateway($paymentMethod->getPaymentGateway());
        $this->paymentGateway->updateCustomerPaymentMethod($detailedPaymentMethod);
        // we can't simply update / replace locally stored payment method information
    }

    /**
     * @param int
     * @param string
     * @return void
     */
    public function delete($userId, $id): void
    {
        $paymentMethods = $this->paymentMethodRepository->get($userId, $id);
        $paymentMethod = $paymentMethods->first();
        if (!$paymentMethod) {
            throw new ModelNotFoundException();
        }
        $this->paymentGateway->detachCustomerPaymentMethod($paymentMethod->getPaymentGatewayPaymentMethodId());
        $this->paymentMethodRepository->delete($userId, $id);
    }
}
