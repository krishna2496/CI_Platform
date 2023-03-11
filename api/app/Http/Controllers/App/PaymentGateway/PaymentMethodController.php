<?php

namespace App\Http\Controllers\App\PaymentGateway;

use App\Events\User\UserActivityLogEvent;
use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use App\Models\PaymentGateway\PaymentGatewayPaymentMethod;
use App\Services\PaymentGateway\CustomerService;
use App\Services\PaymentGateway\PaymentMethodService;
use App\Services\UserService;
use App\Traits\RestExceptionHandlerTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Validator;

class PaymentMethodController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Services\UserService
     */
    private $userService;

    /**
     * @var App\Services\PaymentGateway\CustomerService
     */
    private $customerService;

    /**
     * @var App\Services\PaymentGateway\PaymentMethodService
     */
    private $paymentMethodService;

    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayInterface
     */
    private $paymentGateway;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new controller instance.
     *
     * @param App\Services\PaymentGateway\CustomerService
     * @param App\Services\PaymentGateway\PaymentMethodService
     * @param App\Services\PaymentGateway\CustomerService
     * @param Illuminate\Http\ResponseHelper
     * @return void
     */
    public function __construct(
        UserService $userService,
        CustomerService $customerService,
        PaymentMethodService $paymentMethodService,
        PaymentGatewayFactory $paymentGatewayFactory,
        ResponseHelper $responseHelper
    ) {
        $this->userService = $userService;
        $this->customerService = $customerService;
        $this->paymentMethodService = $paymentMethodService;
        $this->paymentGateway = $paymentGatewayFactory->getPaymentGateway();  // default is STRIPE
        $this->responseHelper = $responseHelper;
    }

    /**
     * Retrieve a list of payment methods.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        try {
            $userId = $request->auth->user_id;

            // Order payment method list with most recent payment method used
            $recent = filter_var(
                $request->get('recent'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            $filters = [
                'recent' => $recent
            ];

            $paymentMethods = $this->paymentMethodService->get(
                $userId,
                null,
                $filters
            );
            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_PAYMENT_METHOD_RETRIEVED'),
                [
                    'payment_methods' => $paymentMethods->all(),
                ],
                false  // do not convert numeric strings.
            );
        } catch (Exception $e) {
            return $this->responseByException($e);
        }
    }

    /**
     * Retrieve a list of payment methods.
     *
     * @param Illuminate\Http\Request $request
     * @param string $id
     * @return Illuminate\Http\JsonResponse
     */
    public function getById(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $request->auth->user_id;
            $paymentMethods = $this->paymentMethodService->get($userId, $id);
            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_PAYMENT_METHOD_RETRIEVED'),
                [
                    'payment_methods' => $paymentMethods->all(),
                ],
                false  // do not convert numeric strings.
            );
        } catch (Exception $e) {
            return $this->responseByException($e);
        }
    }

    /**
     * Create a new payment method entry.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $userId = $request->auth->user_id;
            $validation = Validator::make(
                $request->toArray(),
                [
                    'payment_gateway_payment_method_id' => 'required|min:7|prefix_with:pm_',
                    'payment_gateway' => sprintf('required|in:%s',
                        implode(',', array_keys(config('constants.payment_gateway_types')))
                    ),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->all();
                throw new InvalidArgumentException(implode(' ', $errors));
            }
            $user = $this->userService->findById($userId);
            $paymentMethodId = $request->input('payment_gateway_payment_method_id');
            $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
                ->setUserId($userId)
                ->setPaymentGatewayPaymentMethodId($paymentMethodId)
                ->setPaymentGateway($this->paymentGateway->getType());
            try {
                $customers = $this->customerService->get($userId);
                $customer = $customers->first();
            } catch (ModelNotFoundException $e) {
                $detailedCustomer = (new PaymentGatewayDetailedCustomer)
                    ->setUserId($userId)
                    ->setName(sprintf('%s %s', $user->firstname, $user->lastname))
                    ->setEmail($user->email)
                    ->setPaymentGateway($this->paymentGateway->getType());
                $detailedCustomer = $this->paymentGateway->createCustomer($detailedCustomer);
                $detailedCustomer->setPaymentGatewayCustomerId($detailedCustomer->getPaymentGatewayCustomerId());
                $customer = $this->customerService->create($detailedCustomer);
            }
            $this->paymentGateway->attachCustomerPaymentMethod(
                $customer->getPaymentGatewayCustomerId(),
                $paymentMethodId
            );
            $paymentMethod = $this->paymentMethodService->create($detailedPaymentMethod);

            $this->logActions($request, [
                'id' => $paymentMethod->id,
                'actions' => config('constants.activity_log_actions.CREATED'),
                'data' => [
                    'payment_gateway_payment_method_id' => $request->get('payment_gateway_payment_method_id'),
                    'payment_gateway' => $request->get('payment_gateway')
                ]
            ]);

            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_PAYMENT_METHOD_CREATED')
            );
        } catch (Exception $e) {
            return $this->responseByException($e);
        }
    }

    /**
     * Updates data of a given payment method ID.
     *
     * @param Illuminate\Http\Request $request
     * @param string $id
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $request->auth->user_id;

            if (!count($request->toArray())) {
                throw new InvalidArgumentException('Nothing to update.');
            }

            $validation = Validator::make(
                $request->toArray(),
                [
                    'billing.address_line1' => [
                        'required_with:'.implode(',', [
                            'billing.address_line2',
                            'billing.city',
                            'billing.state',
                            'billing.postal_code',
                            'billing.country',
                        ]),
                        'min:2',
                    ],
                    'billing.address_line2' => 'min:2',
                    'billing.city' => [
                        'required_with:'.implode(',', [
                            'billing.address_line1',
                            'billing.state',
                            'billing.postal_code',
                            'billing.country',
                        ]),
                        'min:2',
                    ],
                    'billing.state' => 'min:2',
                    'billing.postal_code' => 'min:4',
                    'billing.country' => [
                        'required_with:'.implode(',', [
                            'billing.address_line1',
                            'billing.address_line2',
                            'billing.city',
                            'billing.state',
                            'billing.postal_code',
                        ]),
                        'min:2',
                        'max:2',
                    ],
                    'card.name' => [
                        'required_with:'.implode(',', [
                            'card.email',
                            'card.phone',
                            'billing.address_line1',
                            'billing.address_line2',
                            'billing.city',
                            'billing.state',
                            'billing.postal_code',
                            'billing.country',
                        ]),
                        'min:2',
                    ],
                    'card.email' => 'email',
                    'card.phone' => 'min:7',
                    'card.expire_month' => 'required_with:card.expire_year|integer|within_range:1,12',
                    'card.expire_year' => sprintf('required_with:card.expire_month|integer|within_range:%s,2099', date('Y')),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->all();
                throw new InvalidArgumentException(implode(' ', $errors));
            }
            $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
                ->setId($id)
                ->setUserId($userId)
                ->setAddressLine1($request->input('billing.address_line1'))
                ->setAddressLine2($request->input('billing.address_line2'))
                ->setCity($request->input('billing.city'))
                ->setState($request->input('billing.state'))
                ->setPostalCode($request->input('billing.postal_code'))
                ->setCountry($request->input('billing.country'))
                ->setDetails([
                    'name' => $request->input('card.name'),
                    'email' => $request->input('card.email'),
                    'phone' => $request->input('card.phone'),
                    'expire_month' => $request->input('card.expire_month'),
                    'expire_year' => $request->input('card.expire_year'),
                ]);
            $paymentMethod = $this->paymentMethodService->update($detailedPaymentMethod);

            $this->logActions($request, [
                'id' => $id,
                'actions' => config('constants.activity_log_actions.UPDATED'),
                'data' => [
                    'billing' => [
                        'address_line1' => $request->input('billing.address_line1'),
                        'address_line2' => $request->input('billing.address_line2'),
                        'city' => $request->input('billing.city'),
                        'state' => $request->input('billing.state'),
                        'postal_code' => $request->input('billing.postal_code'),
                        'country' => $request->input('billing.country')
                    ],
                    'card' => [
                        'name' => $request->input('card.name'),
                        'email' => $request->input('card.email'),
                        'phone' => $request->input('card.phone'),
                        'expire_month' => $request->input('card.expire_month'),
                        'expire_year' => $request->input('card.expire_year')
                    ]
                ]
            ]);

            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_PAYMENT_METHOD_UPDATED')
            );
        } catch (Exception $e) {
            return $this->responseByException($e);
        }
    }

    /**
     * Removes a payment method by its payment method ID.
     *
     * @param Illuminate\Http\Request $request
     * @param string $id
     * @return Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $request->auth->user_id;
            $this->paymentMethodService->delete($userId, $id);

            $this->logActions($request, [
                'id' => $id,
                'actions' => config('constants.activity_log_actions.DELETED'),
                'data' => null
            ]);

            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_PAYMENT_METHOD_DELETED')
            );
        } catch (Exception $e) {
            return $this->responseByException($e);
        }
    }

    /**
     * @param Exception
     * @return Illuminate\Http\JsonResponse
     */
    protected function responseByException(Exception $exception): JsonResponse
    {
        if ($exception instanceof ModelNotFoundException) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_PAYMENT_METHOD_NOT_FOUND'),
                trans('messages.custom_error_message.MESSAGE_PAYMENT_METHOD_NOT_FOUND')
            );
        }
        if ($exception instanceof PaymentGatewayException) {
            switch ($exception->getPaymentGateway()) {
                case config('constants.payment_gateway_types.STRIPE'):
                    return $this->paymentGateway->getResponseByException($exception);
                    break;

                default:
                    break;
            }
        }
        if ($exception instanceof InvalidArgumentException) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_METHOD_INVALID_DATA'),
                $exception->getMessage()
            );
        }
        return $this->responseHelper->error(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
            config('constants.error_codes.ERROR_PAYMENT_METHOD_UNKNOWN_ERROR'),
            $exception->getMessage()
        );
    }

    /**
     * Add event user logs
     *
     * @param Request $request
     * @param array $payload
     *              $payload['actions'] method actions. Ex. (CREATE, UPDATE, DELETE)
     *              $payload['id'] Object ID processed payment method
     *              $payload['data'] All endpoint request data (The required data only)
     *
     * @return Illuminate\Http\JsonResponse
     */
    private function logActions(Request $request, $payload)
    {
        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.PAYMENT_METHOD'),
            $payload['actions'] ?? null,
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email ?? null,
            get_class($this),
            $payload['data'] ?? null,
            $request->auth->user_id ?? null,
            $payload['id'] ?? null
        ));
    }
}
