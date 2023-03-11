<?php

namespace App\Http\Controllers\Admin\Organization;

use App\Events\User\UserActivityLogEvent;
use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Models\Organization;
use App\Models\PaymentGateway\PaymentGatewayAccount;
use App\Repositories\Organization\OrganizationRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Services\PaymentGateway\AccountService;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use Validator;

//!  Organization controller
/*!
This controller is responsible for handling organization listing, show, store, update and delete operations.
 */
class OrganizationController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Repositories\Organization\OrganizationRepository
     */
    private $organizationRepository;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * @var array
     */
    private $paymentGateways;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    public $tenantActivatedSettingRepository;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var PaymentGatewayFactory
     */
    private $paymentGatewayFactory;

    /**
     * @var bool
     */
    private $donationTenantSettingValueCache;

    /**
     * Create a new controller instance.
     *
     * @param App\Helpers\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(
        ResponseHelper $responseHelper,
        OrganizationRepository $organizationRepository,
        AccountService $accountService,
        PaymentGatewayFactory $paymentGatewayFactory,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        Request $request
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->accountService = $accountService;
        $this->paymentGatewayFactory = $paymentGatewayFactory;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->responseHelper = $responseHelper;
        $this->paymentGateways = array_keys(config('constants.payment_gateway_types'));
        $this->userApiKey =$request->header('php-auth-user');
    }

    protected function isDonationTenantSettingEnabled($request)
    {
        if (!isset($this->donationTenantSettingValueCache)) {
            $this->donationTenantSettingValueCache = $this->tenantActivatedSettingRepository
                ->checkTenantSettingStatus(config('constants.tenant_settings.DONATION'), $request);
        }
        return $this->donationTenantSettingValueCache;
    }

    /**
     * Fetch all organizations
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $organizations = $this->organizationRepository->getOrganizationList($request);

            if (!$this->isDonationTenantSettingEnabled($request)) {
                foreach ($organizations as $organization) {
                    $organization->setHidden(['paymentGatewayAccount']);
                }
            }

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($organizations->isEmpty()) ? trans('messages.custom_error_message.ERROR_ORGANIZATION_NOT_FOUND')
            : trans('messages.success.MESSAGE_ORGANIZATION_LISTING');

            return $this->responseHelper->successWithPagination(
                $apiStatus,
                $apiMessage,
                $organizations,
                [],
                false
            );
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * View organization
     *
     * @param Illuminate\Http\Request $request
     * @param string $organizationId
     * @return Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $organizationId): JsonResponse
    {
        try {
            // Get organization details
            $organization = $this->organizationRepository->getOrganizationDetails($organizationId);

            if (!$this->isDonationTenantSettingEnabled($request)) {
                $organization->setHidden(['paymentGatewayAccount']);
            }

            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_FOUND');

            return $this->responseHelper->success(
                $apiStatus,
                $apiMessage,
                $organization->toArray(),
                false
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_ORGANIZATION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_ORGANIZATION_NOT_FOUND')
            );
        }
    }

    /**
     * Store organization
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validations
        $validation = [
            'name' => 'required|max:255',
            'legal_number' => 'max:255',
            'phone_number' => 'max:120',
            'address_line_1' => 'max:255',
            'address_line_2' => 'max:255',
            'city_id' => 'numeric|exists:city,city_id,deleted_at,NULL',
            'country_id' => 'numeric|exists:country,country_id,deleted_at,NULL',
            'postal_code' => 'max:120',
        ];
        if ($this->isDonationTenantSettingEnabled($request)) {
            $gateways = implode(',', $this->paymentGateways);
            $validation = array_merge($validation, [
                'payment_gateway_account.payment_gateway' => "required_with:payment_gateway_account.payment_gateway_account_id|in:$gateways",
                'payment_gateway_account.payment_gateway_account_id' => 'required_with:payment_gateway_account.payment_gateway',
            ]);
        }
        $validator = Validator::make($request->all(), $validation);

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_ORGANIZATION_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        // update city,state & country id to null if it's blank
        if ($request->has('city_id') && $request->get('city_id')=='') {
            $request->merge(['city_id' => null]);
        }
        if ($request->has('country_id') && $request->get('country_id')=='') {
            $request->merge(['country_id' => null]);
        }

        if ($this->isDonationTenantSettingEnabled($request)) {
            $gatewayAccount = $request->get('payment_gateway_account');
            if (!empty($gatewayAccount)) {
                $validateAccount = $this->validatePaymentGatewayAccount($gatewayAccount['payment_gateway_account_id']);
                if (!$validateAccount['valid']) {
                    return $this->responseHelper->error(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                        config('constants.error_codes.ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID'),
                        $validateAccount['error']
                    );
                }
            }
        }

        // Create a new record
        $organization = $this->organizationRepository->store($request);
        if ($this->isDonationTenantSettingEnabled($request)) {
            if ($request->has('payment_gateway_account') && !empty($gatewayAccount)) {
                $this->savePaymentGatewayAccount(
                    $organization,
                    $gatewayAccount
                );
            }
        }

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.ORGANIZATION'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            $organization->organization_id
        ));

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_CREATED');
        $apiData = ['organization_id' => $organization->organization_id];
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update organization
     *
     * @param Illuminate\Http\Request $request
     * @param string $organizationId
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $organizationId): JsonResponse
    {
        try {
            // Server side validations
            $validation = [
                'name' => 'sometimes|required|max:255',
                'legal_number' => 'max:255',
                'phone_number' => 'max:120',
                'address_line_1' => 'max:255',
                'address_line_2' => 'max:255',
                'city_id' => 'numeric|exists:city,city_id,deleted_at,NULL',
                'country_id' => 'numeric|exists:country,country_id,deleted_at,NULL',
                'postal_code' => 'max:120',
            ];
            $organizationDetails = array_keys($validation);
            if ($this->isDonationTenantSettingEnabled($request)) {
                $gateways = implode(',', $this->paymentGateways);
                $validation = array_merge($validation, [
                    'payment_gateway_account.payment_gateway' => "required_with:payment_gateway_account.payment_gateway_account_id|in:$gateways",
                    'payment_gateway_account.payment_gateway_account_id' => 'required_with:payment_gateway_account.payment_gateway',
                ]);
            }
            $validator = Validator::make($request->all(), $validation);

            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_ORGANIZATION_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            $organizationDetails = array_intersect($organizationDetails, array_keys($request->all()));

            // update city,state & country id to null if it's blank
            if ($request->has('city_id') && $request->get('city_id')=='') {
                $request->merge(['city_id' => null]);
            }
            if ($request->has('country_id') && $request->get('country_id')=='') {
                $request->merge(['country_id' => null]);
            }

            $gatewayAccount = $request->get('payment_gateway_account');
            $hasGatewayAccount = $request->has('payment_gateway_account');

            if ($this->isDonationTenantSettingEnabled($request)) {
                if ($hasGatewayAccount && !empty($gatewayAccount)) {
                    $validateAccount = $this->validatePaymentGatewayAccount($gatewayAccount['payment_gateway_account_id']);
                    if (!$validateAccount['valid']) {
                        return $this->responseHelper->error(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                            config('constants.error_codes.ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID'),
                            $validateAccount['error']
                        );
                    }
                }
            }

            // Only update organization details if column name is supplied.
            if (empty($organizationDetails)) {
                $organization = $this->organizationRepository->find($organizationId);
            } else {
                $organization = $this->organizationRepository->update($request, $organizationId);
            }

            if ($this->isDonationTenantSettingEnabled($request) && $hasGatewayAccount) {
                if (empty($gatewayAccount)) {
                    $isLinked = $this->organizationRepository->isLinkedToMissionWithDonationAttribute($organizationId);
                    if ($isLinked) {
                        return $this->responseHelper->error(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                            config('constants.error_codes.ERROR_ORGANIZATION_UPDATE_WITHOUT_ACCOUNT'),
                            'Cannot unset the organization’s payment_gateway_account as it is linked to a donation mission.'
                        );
                    }
                    $this->accountService->delete([
                        'organization_id' => $organizationId
                    ]);
                } else {
                    $this->savePaymentGatewayAccount(
                        $organization,
                        $gatewayAccount
                    );
                }
            }

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.ORGANIZATION'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $organization->organization_id
            ));
            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_UPDATED');
            $apiData = ['organization_id' => $organization->organization_id];
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_ORGANIZATION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_ORGANIZATION_NOT_FOUND')
            );
        }
    }

    /**
     * Save organization payment gateway account
     *
     * @param Organization $organization
     * @param array $gatewayAccount
     *
     * @return PaymentGatewayAccount
     */
    public function savePaymentGatewayAccount(Organization $organization, array $gatewayAccount): PaymentGatewayAccount
    {
        $paymentGatewayAccount = new PaymentGatewayAccount();
        $paymentGatewayAccount
            ->setAttribute('organization_id', $organization->organization_id)
            ->setAttribute('payment_gateway_account_id', $gatewayAccount['payment_gateway_account_id'])
            ->setAttribute('payment_gateway', config('constants.payment_gateway_types.'.$gatewayAccount['payment_gateway']));

        // Create or Update Payment Gateway Account
        return $this->accountService->save($paymentGatewayAccount);
    }

    /**
     * Delete organization
     *
     * @param string $organizationId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(string $organizationId): JsonResponse
    {
        try {
            $isOrganizationLinked = $this->organizationRepository->isOrganizationLinkedtoMission($organizationId);
            if ($isOrganizationLinked) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_ORGANIZATION_LINKED_TO_MISSION'),
                    trans('messages.custom_error_message.ERROR_ORGANIZATION_LINKED_TO_MISSION')
                );
            }
            //Delete organization
            $organization = $this->organizationRepository->delete($organizationId);

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.ORGANIZATION'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                [],
                null,
                $organizationId
            ));
            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_DELETED');

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_ORGANIZATION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_ORGANIZATION_NOT_FOUND')
            );
        }
    }

    /**
     * Validates provided payment gateway account if valid or not
     *
     * @param string $accountId
     * @return array
     */
    private function validatePaymentGatewayAccount(string $accountId): array
    {
        $paymentGateway = $this->paymentGatewayFactory->getPaymentGateway(
            config('constants.payment_gateway_types.STRIPE')
        );

        try {
            $account = $paymentGateway->getAccount($accountId);
            if (!$account->getPayoutsEnabled()) {
                return [
                    'valid' => false,
                    'error' => 'Account payouts is not enabled'
                ];
            }
            return [
                'valid' => true
            ];
        } catch (PaymentGatewayException $e) {
            return [
                'valid' => false,
                'error' => 'Invalid payment gateway account id'
            ];
        }
    }
}
