<?php

namespace App\Http\Controllers\App\PaymentGateway;

use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Libraries\Amount;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedAccount;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment as DetailedPayment;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use App\Models\Donation;
use App\Models\Mission;
use App\Models\PaymentGateway\Payment;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\Organization\OrganizationRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Repositories\User\UserRepository;
use App\Services\Donation\DonationService;
use App\Services\PaymentGateway\CustomerService;
use App\Services\PaymentGateway\PaymentMethodService;
use App\Services\PaymentGateway\PaymentService;
use App\Traits\RestExceptionHandlerTrait;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Torann\GeoIP\Facades\GeoIP;
use Validator;

class PaymentController extends Controller
{
    use RestExceptionHandlerTrait;

    const ONE_HUNDRED_PERCENT_DONATION = '100_percent_donation';

    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayFactory
     */
    private $paymentGatewayFactory;

    /**
     * @var App\Services\PaymentGateway\PaymentService
     */
    private $paymentService;

    /**
     * @var App\Service\PaymentGateway\CustomerService
     */
    private $customerService;

    /**
     * @var App\Service\PaymentGateway\PaymentMethodService
     */
    private $paymentMethodService;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    private $missionRepository;

    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Repositories\Organization\OrganizationRepository
     */
    private $organizationRepository;

    /**
     * @var App\Services\Donation\DonationService
     */
    private $donationService;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new payment controller instance
     *
     * @param PaymentGatewayFactory $paymentGatewayFactory
     * @param PaymentService $paymentService
     * @param CustomerService $customerService
     * @param TenantActivatedSettingRepository
     * @param MissionRepository $missionRepository
     * @param UserRepository $userRepository
     * @param OrganizationRepository $organizationRepository
     * @param DonationService $donationService
     * @param Helpers $helpers
     * @param ResponseHelper $responseHelper
     */
    public function __construct(
        PaymentGatewayFactory $paymentGatewayFactory,
        PaymentService $paymentService,
        CustomerService $customerService,
        PaymentMethodService $paymentMethodService,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        MissionRepository $missionRepository,
        UserRepository $userRepository,
        OrganizationRepository $organizationRepository,
        DonationService $donationService,
        Helpers $helpers,
        ResponseHelper $responseHelper
    ) {
        $this->paymentGatewayFactory = $paymentGatewayFactory;
        $this->paymentService = $paymentService;
        $this->customerService = $customerService;
        $this->paymentMethodService = $paymentMethodService;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->missionRepository = $missionRepository;
        $this->userRepository = $userRepository;
        $this->organizationRepository = $organizationRepository;
        $this->donationService = $donationService;
        $this->helpers = $helpers;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Create a new payment record
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $paymentGateways = implode(',', array_keys(
            config('constants.payment_gateway_types')
        ));
        $paymentMethods = implode(',', array_keys(
            config('constants.payment_method_types')
        ));

        // determine if setting for 100 percent donation is enabled
        $hundredPercentSetting = $this->tenantActivatedSettingRepository->checkTenantSettingStatus(
            self::ONE_HUNDRED_PERCENT_DONATION,
            $request
        );

        $validator = Validator::make(
            $request->all(),
            [
                'payment_gateway' => [Rule::requiredIf(!$request->input('payment_method_id')), 'in:'.$paymentGateways],
                'payment_method_type' => [Rule::requiredIf(!$request->input('payment_method_id')), 'in:'.$paymentMethods],
                'currency' => 'required|min:3|max:3', // TODO: Get from user data
                'amount' => 'required|integer|min:1',
                'cover_fee' => [Rule::requiredIf(!$hundredPercentSetting), 'boolean'],
                'billing' => Rule::requiredIf(!$request->input('payment_method_id')),
                'billing.name' => 'required_with:billing|max:100',
                'billing.phone' => 'max:30',
                'billing.address_line_1' => 'required_with:billing|max:255',
                'billing.address_line_2' => 'required_with:billing|max:255',
                'billing.city' => 'required_with:billing|max:100',
                'billing.state' => 'required_with:billing|max:100',
                'billing.country' => 'required_with:billing|min:2|max:2',
                'billing.postal_code' => 'required_with:billing|max:20',
                'payment_method_id' => 'sometimes|exists:payment_gateway_payment_method,id,deleted_at,NULL',
                'mission_id' => 'required|integer|exists:mission,mission_id,deleted_at,NULL'
            ]
        );

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_PAYMENT_DATA'),
                $validator->errors()->first()
            );
        }

        try {
            $user = $this->userRepository->findUserDetail($request->auth->user_id);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }

        $mission = $this->missionRepository->find($request->input('mission_id'));
        $eligibleForDonation = $this->missionRepository->isEligibleForDonation(
            $request,
            $mission->mission_id
        );

        if (!$eligibleForDonation) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_MISSION_NOT_ELIGIBLE_FOR_DONATION'),
                trans('messages.custom_error_message.ERROR_PAYMENT_MISSION_NOT_ELIGIBLE_FOR_DONATION')
            );
        }

        try {
            $organizationId = $mission->organization->organization_id;
            $organization = $this->organizationRepository->getOrganizationDetails(
                $organizationId
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_ORGANIZATION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_ORGANIZATION_NOT_FOUND')
            );
        }

        // check if mission's organization has a payment gateway account (can accept donations)
        if (!$organization->paymentGatewayAccount) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_ORGANIZATION_DOES_NOT_SUPPORT_DONATION'),
                trans('messages.custom_error_message.ERROR_PAYMENT_ORGANIZATION_DOES_NOT_SUPPORT_DONATION')
            );
        }

        $paymentMethod = null;
        if ($request->input('payment_method_id')) {
            $paymentMethod = $this->getPaymentMethod($request);
            if (!$paymentMethod) { // invalid payment method
                return $this->modelNotFound(
                    config('constants.error_codes.ERROR_PAYMENT_METHOD_NOT_FOUND'),
                    trans('messages.custom_error_message.ERROR_PAYMENT_METHOD_NOT_FOUND')
                );
            }
        }

        $paymentGateway = $this->paymentGatewayFactory->getPaymentGateway(
            $paymentMethod
            ? $paymentMethod->getPaymentGateway()
            : config('constants.payment_gateway_types.'.$request->input('payment_gateway'))
        );

        $customer = $this->getCustomer($request, $paymentGateway, $user);
        $billing = $request->input('billing');
        $ipAddressCountry = GeoIP::getLocation($request->ip());
        $tenant = $this->helpers->getTenantIdAndSponsorIdFromRequest($request);

        $payment = (new Payment())
            ->setAttribute(
                'payment_gateway',
                $paymentMethod
                    ? $paymentMethod->getPaymentGateway()
                    : config('constants.payment_gateway_types.'.$request->input('payment_gateway'))
            )
            ->setAttribute(
                'payment_method_type',
                $paymentMethod
                    ? config('constants.payment_method_types.'.strtoupper($paymentMethod->getPaymentGatewayPaymentMethodType()))
                    : config('constants.payment_method_types.'.$request->input('payment_method_type'))
            )
            ->setAttribute('currency', $request->input('currency'))
            ->setAttribute(
                'payment_gateway_account_id',
                $organization->paymentGatewayAccount->payment_gateway_account_id
            )
            ->setAttribute(
                'billing_name',
                $paymentMethod ? $paymentMethod->getDetails()['name'] : $billing['name']
            )
            ->setAttribute(
                'billing_email',
                $paymentMethod ? $paymentMethod->getDetails()['email'] ?? $user->email : $user->email
            )
            ->setAttribute(
                'billing_phone',
                $paymentMethod ? $paymentMethod->getDetails()['phone'] : $billing['phone']
            )
            ->setAttribute(
                'billing_address_line_1',
                $paymentMethod ? $paymentMethod->getAddressLine1() : $billing['address_line_1']
            )
            ->setAttribute(
                'billing_address_line_2',
                $paymentMethod ? $paymentMethod->getAddressLine2() : $billing['address_line_2']
            )
            ->setAttribute(
                'billing_city',
                $paymentMethod ? $paymentMethod->getCity() : $billing['city']
            )
            ->setAttribute(
                'billing_state',
                $paymentMethod ? $paymentMethod->getState() : $billing['state']
            )
            ->setAttribute(
                'billing_country',
                $paymentMethod ? $paymentMethod->getCountry() : $billing['country']
            )
            ->setAttribute(
                'billing_postal_code',
                $paymentMethod ? $paymentMethod->getPostalCode() : $billing['postal_code']
            )
            ->setAttribute('ip_address', $request->ip())
            ->setAttribute(
                'ip_address_country',
                $ipAddressCountry->default ? null : $ipAddressCountry->iso_code
            )
            ->setAttribute(
                'payment_gateway_payment_method_id',
                $paymentMethod ? $paymentMethod->getId() : null
            );

        if (!$hundredPercentSetting) {
            $payment->setAttribute('cover_fee', $request->input('cover_fee'));
        }

        $detailedPayment = (new DetailedPayment())
            ->setUserId($request->auth->user_id)
            ->setAmountDonated(new Amount($request->input('amount')))
            ->setMissionId($mission->mission_id)
            ->setHundredPercentSetting($hundredPercentSetting)
            ->setCustomerId($customer->getPaymentGatewayCustomerId())
            ->setOrganizationId($organization->organization_id)
            ->setTenantId($tenant->tenant_id)
            ->setConnectedAccountId(
                $organization->paymentGatewayAccount->payment_gateway_account_id
            )
            ->setPayment($payment);

        if ($paymentMethod) {
            $detailedPayment->setPaymentMethodId(
                $paymentMethod->getPaymentGatewayPaymentMethodId()
            );
        }

        try {
            $detailedPayment = $paymentGateway->computeChargesAndFees(
                $detailedPayment
            );

            // create payment gateway's payment request
            $paymentGatewayPaymentResult = $paymentGateway->createPayment($detailedPayment);
            $payment->setAttribute(
                'payment_gateway_payment_id',
                $paymentGatewayPaymentResult->getPaymentGatewayPaymentId()
            );

            // create local payment record
            $paymentCreated = $this->paymentService->create($payment);
            // create donation record if payment creation has succeeded
            if ($paymentCreated->id) {
                $donation = (new Donation())
                    ->setAttribute('mission_id', $request->input('mission_id'))
                    ->setAttribute('payment_id', $paymentCreated->id)
                    ->setAttribute('organization_id', $organization->organization_id)
                    ->setAttribute('user_id', $request->auth->user_id);
                $this->donationService->create($donation);
            }

            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_PAYMENT_SUCCESSFULLY_CREATED'),
                [
                    'id' => $paymentCreated->id,
                    'client_secret' => $paymentGatewayPaymentResult->getClientSecret()
                ]
            );

        } catch (PaymentGatewayException $e) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                trans('messages.custom_error_message.ERROR_FAILED_CREATING_PAYMENT_OBJECT'),
                $e->getMessage()
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_PAYMENT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_PAYMENT_NOT_FOUND')
            );
        } catch (Exception $e) {
            return $this->responseHelper->error(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                config('constants.error_codes.ERROR_FAILED_SAVING_PAYMENT_RECORD'),
                trans('messages.custom_error_message.ERROR_FAILED_SAVING_PAYMENT_RECORD')
            );
        }
    }

    /**
     * Gets the customer record of user
     * If there's no customer record yet, create one
     *
     * @param Request $request
     * @param PaymentGatewayInterface $paymentGateway
     * @param User $user
     *
     * @return PaymentGatewayDetailedCustomer
     */
    private function getCustomer(
        Request $request,
        PaymentGatewayInterface $paymentGateway,
        User $user
    ): PaymentGatewayDetailedCustomer
    {
        $userId = $request->auth->user_id;
        try {
            return $this->customerService->get($userId)->first();
        } catch (ModelNotFoundException $e) {
            // if user has no customer record, create one
            $detailedCustomer = (new PaymentGatewayDetailedCustomer)
                ->setUserId($userId)
                ->setName($user->first_name . ' ' . $user->last_name)
                ->setEmail($user->email)
                ->setPaymentGateway($paymentGateway->getType());

            $detailedCustomer = $paymentGateway->createCustomer($detailedCustomer);
            return $this->customerService->create($detailedCustomer);
        }
    }

    /**
     * Validates and fetch details of the payment method if provided
     *
     * @param Request $request
     *
     * @return PaymentGatewayDetailedPaymentMethod|false
     */
    private function getPaymentMethod(Request $request)
    {
        try {
            $paymentMethod = $this->paymentMethodService->get(
                $request->auth->user_id,
                $request->input('payment_method_id')
            );
            return $paymentMethod->first();
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }
}