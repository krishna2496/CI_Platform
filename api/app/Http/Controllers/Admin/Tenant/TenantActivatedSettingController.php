<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Events\User\UserActivityLogEvent;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\TenantActivatedSetting;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Exceptions\VolunteeringTimeOrGoalSettingShouldBeActiveException;

//!  Tenant activated setting controller
/*!
This controller is responsible for handling tenant activated setting store/delete operation.
 */
class TenantActivatedSettingController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var  App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers
    ) {
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
    }

    /**
     * Display a listing of activated tenant settings.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Fetch all tenant settings details from super admin
        $tenantSettings = $this->helpers->getAllTenantSetting($request)->keyBy('tenant_setting_id');
        $keys = $tenantSettings
            ->keys()
            ->toArray();

        // Fetch all activated tenant settings data
        $activatedTenantSettings = $this->tenantActivatedSettingRepository->getList(
            $keys
        );

        $settings = $activatedTenantSettings->map(function ($setting) use ($tenantSettings) {
            $info = $tenantSettings->get($setting->setting_id);
            if (!$info) {
                return null;
            }
            $info->tenant_setting_id = $setting->tenant_setting_id;
            return $info;
        })->filter();

        // Set response data
        $apiData = $settings->isEmpty() ? [] : $settings->toArray();
        $apiStatus = Response::HTTP_OK;
        $apiMessage = $settings->isEmpty() ? trans('messages.success.MESSAGE_NO_RECORD_FOUND') : trans('messages.success.MESSAGE_TENANT_SETTINGS_LISTING');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Store a newly created tenant activated settings into database
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse;
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->toArray(), [
            'settings' => 'required',
            'settings.*.tenant_setting_id' => 'required|exists:tenant_setting,tenant_setting_id,deleted_at,NULL',
            'settings.*.value' => 'required|in:0,1',
            ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_SETTING_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        // Check volunteering setting is disabled or not
        $response = $this->tenantActivatedSettingRepository->checkVolunteeringSettingDisabled($request->toArray());
        if (!$response) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_VOLUNTEERING_SHOULD_BE_ENABLED'),
                trans('messages.custom_error_message.ERROR_VOLUNTEERING_SHOULD_BE_ENABLED')
            );
        }

        try {
            // Store settings
            $response = $this->tenantActivatedSettingRepository->store($request->toArray());
        } catch (VolunteeringTimeOrGoalSettingShouldBeActiveException $e) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_VOLUNTEERING_TIME_OR_GOAL_SHOULD_BE_ACTIVE'),
                trans('messages.custom_error_message.ERROR_VOLUNTEERING_TIME_OR_GOAL_SHOULD_BE_ACTIVE')
            );
        }

        $requestArray = $request->toArray();

        foreach ($requestArray['settings'] as $requestData) {
            $activityLogStatus = $requestData['value'] == 1 ?
                config('constants.activity_log_actions.CREATED') : config('constants.activity_log_actions.DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.TENANT_SETTINGS'),
                $activityLogStatus,
                config('constants.activity_log_user_types.API'),
                $request->header('php-auth-user'),
                get_class($this),
                $request->toArray(),
                null,
                $requestData['tenant_setting_id']
            ));
        }

        // Set response data
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_SETTINGS_UPDATED');

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
