<?php

namespace App\Http\Controllers;

use App\Events\ActivityLogEvent;
use App\Exceptions\CannotDeactivateDefaultTenantCurrencyException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Currency\TenantAvailableCurrencyRepository;
use App\Repositories\Tenant\TenantRepository;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Validator;

//!  TenantCurrencyController controller
/*!
This controller is responsible for handling currency setting store/delete and show operations.
 */
class TenantCurrencyController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\Currency\TenantAvailableCurrencyRepository
     */
    private $tenantAvailableCurrencyRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Repositories\Tenant\TenantRepository
     */
    private $tenantRepository;

    /**
     * @var App\Repositories\Currency\CurrencyRepository;
     */
    private $currencyRepository;

    /**
     * Create a new Tenant currency controller instance.
     *
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Repositories\Currency\TenantAvailableCurrencyRepository $tenantAvailableCurrencyRepository
     * @param App\Repositories\Tenant\TenantRepository $tenantRepository
     * @param App\Repositories\Currency\CurrencyRepository $currencyRepository
     * @return App\Http\Controllers\TenantCurrencyController
     */
    public function __construct(
        ResponseHelper $responseHelper,
        TenantAvailableCurrencyRepository $tenantAvailableCurrencyRepository,
        TenantRepository $tenantRepository,
        CurrencyRepository $currencyRepository
    ) {
        $this->responseHelper = $responseHelper;
        $this->tenantAvailableCurrencyRepository = $tenantAvailableCurrencyRepository;
        $this->tenantRepository = $tenantRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * List tenant’s currency
     *
     * @param Request $request
     * @param int $tenantId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function index(Request $request, int $tenantId): JsonResponse
    {
        try {
            $perPage = $request->perPage;
            $tenantCurrencyList = $this->tenantAvailableCurrencyRepository->getTenantCurrencyList($perPage, $tenantId);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiData = $tenantCurrencyList;

            $apiMessage = (count($apiData) > 0)  ?
                trans('messages.success.MESSAGE_TENANT_CURRENCY_LISTING') :
                trans('messages.custom_error_message.ERROR_TENANT_CURRENCY_EMPTY_LIST');

            return $this->responseHelper->successWithPagination($apiData, $apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }
    }

    /**
     * Store a newly created tenant currency into database
     *
     * @param \Illuminate\Http\Request $request
     * @param int $tenantId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function store(Request $request, int $tenantId): JsonResponse
    {
        try {
            $this->tenantRepository->find($tenantId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }

        $validator = Validator::make($request->toArray(), [
            'code' => [
                'required',
                'regex:/^[A-Z]{3}$/',
                Rule::unique('tenant_currency')->where(function ($query) use ($tenantId, $request) {
                    $query->where(['tenant_id' => $tenantId]);
                })],
            'default' => 'boolean',
            'is_active' => 'required|boolean',
        ]);

        if ($request['is_active'] == false && $request['default'] == true) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_IS_ACTIVE_FIELD_MUST_BE_TRUE'),
                trans('messages.custom_error_message.ERROR_IS_ACTIVE_FIELD_MUST_BE_TRUE')
            );
        }

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_CURRENCY_FIELD_REQUIRED'),
                $validator->errors()->first()
            );
        }

        $isCurrencySupported = $this->currencyRepository->isSupported($request['code']);
        if (!$isCurrencySupported) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_CURRENCY_CODE_NOT_AVAILABLE'),
                trans('messages.custom_error_message.ERROR_CURRENCY_CODE_NOT_AVAILABLE')
            );
        }

        $currencyData = [
            'code' => $request['code'],
            'is_active' => $request['is_active']
        ];

        if (isset($request->default)) {
            $currencyData['default'] = $request->default;
        }

        // Store tenant currency details
        $this->tenantAvailableCurrencyRepository->store($currencyData, $tenantId);

        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_CURRENCY_ADDED');

        // Make activity log
        event(new ActivityLogEvent(
            config('constants.activity_log_types.TENANT_CURRENCY'),
            config('constants.activity_log_actions.CREATED'),
            get_class($this),
            $request->toArray(),
            $tenantId
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }

    /**
     * Update tenant currency for tenant into database
     *
     * @param Request $request
     * @param int $tenantId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function update(Request $request, int $tenantId): JsonResponse
    {
        try {
            $this->tenantRepository->find($tenantId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }

        $validator = Validator::make($request->toArray(), [
            'code' => 'required|regex:/^[A-Z]{3}$/',
            'default' => 'boolean',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_CURRENCY_FIELD_REQUIRED'),
                $validator->errors()->first()
            );
        }

        if ($request['is_active'] == false && $request['default'] == true) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_IS_ACTIVE_FIELD_MUST_BE_TRUE'),
                trans('messages.custom_error_message.ERROR_IS_ACTIVE_FIELD_MUST_BE_TRUE')
            );
        }

        $isCurrencySupported = $this->currencyRepository->isSupported($request['code']);
        if (!$isCurrencySupported) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_CURRENCY_CODE_NOT_AVAILABLE'),
                trans('messages.custom_error_message.ERROR_CURRENCY_CODE_NOT_AVAILABLE')
            );
        }

        $currencyData = [
            'code' => $request['code'],
            'is_active' => $request['is_active']
        ];

        if (isset($request->default)) {
            $currencyData['default'] = $request->default;
        }

        try {
            $this->tenantAvailableCurrencyRepository->update($currencyData, $tenantId);
        } catch (CannotDeactivateDefaultTenantCurrencyException $e) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_DEFAULT_CURRENCY_SHOULD_BE_ACTIVE'),
                trans('messages.custom_error_message.ERROR_DEFAULT_CURRENCY_SHOULD_BE_ACTIVE')
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.CURRENCY_CODE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_CURRENCY_CODE_NOT_FOUND')
            );
        }

        // Update tenant currency details
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_CURRENCY_UPDATED');

        // Make activity log
        event(new ActivityLogEvent(
            config('constants.activity_log_types.TENANT_CURRENCY'),
            config('constants.activity_log_actions.UPDATED'),
            get_class($this),
            $request->toArray(),
            $tenantId
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
