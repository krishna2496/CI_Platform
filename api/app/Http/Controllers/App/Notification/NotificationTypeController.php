<?php

namespace App\Http\Controllers\App\Notification;

use App\Repositories\NotificationType\NotificationTypeRepository;
use App\Traits\RestExceptionHandlerTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Validator;
use App\Events\User\UserActivityLogEvent;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;

//!  Notification type controller
/*!
This controller is responsible for handling notification type listing and store/update operations.
 */
class NotificationTypeController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\NotificationType\NotificationTypeRepository
     */
    private $notificationTypeRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * Create a new notification type controller instance.
     *
     * @param App\Repositories\NotificationType\NotificationTypeRepository $notificationTypeRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @return void
     */
    public function __construct(
        NotificationTypeRepository $notificationTypeRepository,
        ResponseHelper $responseHelper,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository
    ) {
        $this->notificationTypeRepository = $notificationTypeRepository;
        $this->responseHelper = $responseHelper;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
    }

    /**
     * Fetch notification settings.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        //Fetch notification settings
        $notificationSettings = $this->notificationTypeRepository->getNotificationSettings($request->auth->user_id);

        // Fetch tenant activated settings
        $getActivatedTenantSettings = $this->tenantActivatedSettingRepository
        ->getAllTenantActivatedSetting($request);

        $notificationSettings = $notificationSettings->toArray();
        $enabledNotificationSettings = array();
        foreach ($notificationSettings as $key => $value) {
            switch ($value['notification_type']) {
                case "my_stories":
                case "recommended_story":
                    $tenantSetting = config('constants.tenant_settings.STORIES_ENABLED');
                    if (in_array($tenantSetting, $getActivatedTenantSettings)) {
                        $enabledNotificationSettings[] = $value;
                    }
                    break;
                case "recommended_missions":
                    $tenantSetting = config('constants.tenant_settings.INVITE_COLLEAGUE');
                    if (in_array($tenantSetting, $getActivatedTenantSettings)) {
                        $enabledNotificationSettings[] = $value;
                    }
                    break;
                case "new_news":
                    $tenantSetting = config('constants.tenant_settings.NEWS_ENABLED');
                    if (in_array($tenantSetting, $getActivatedTenantSettings)) {
                        $enabledNotificationSettings[] = $value;
                    }
                    break;
                case "new_messages":
                    $tenantSetting = config('constants.tenant_settings.MESSAGE_ENABLED');
                    if (in_array($tenantSetting, $getActivatedTenantSettings)) {
                        $enabledNotificationSettings[] = $value;
                    }
                    break;
                case "my_comments":
                    $tenantSetting = config('constants.tenant_settings.MISSION_COMMENTS');
                    if (in_array($tenantSetting, $getActivatedTenantSettings)) {
                        $enabledNotificationSettings[] = $value;
                    }
                    break;
                case 'volunteering_hours':
                    $volunteerSetting = config('constants.tenant_settings.VOLUNTEERING_MISSION');
                    $timeSetting = config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION');
                    if (in_array($volunteerSetting, $getActivatedTenantSettings)
                        && in_array($timeSetting, $getActivatedTenantSettings)
                    ) {
                        $enabledNotificationSettings[] = $value;
                    }
                    break;
                case 'volunteering_goals':
                    $volunteerSetting = config('constants.tenant_settings.VOLUNTEERING_MISSION');
                    $goalSetting = config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION');
                    if (in_array($volunteerSetting, $getActivatedTenantSettings)
                        && in_array($goalSetting, $getActivatedTenantSettings)
                    ) {
                        $enabledNotificationSettings[] = $value;
                    }
                    break;
                case 'mission_application':
                    $tenantSetting = config('constants.tenant_settings.VOLUNTEERING_MISSION');
                    if (in_array($tenantSetting, $getActivatedTenantSettings)) {
                        $enabledNotificationSettings[] = $value;
                    }
                    break;
                default:
                    $enabledNotificationSettings[] = $value;
            }
        }

        // Set response data
        $apiData = $enabledNotificationSettings;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_NOTIFICATION_SETTINGS_LISTING');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Store or update user notification settings into database
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse;
     */
    public function storeOrUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->toArray(), [
            'settings' => 'required',
            'settings.*.notification_type_id' =>
            'required|exists:notification_type,notification_type_id,deleted_at,NULL',
            'settings.*.value' => 'required|in:0,1',
            'user_settings' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_NOTIFICATION_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        // Store or update user notification settings
        $notificationSettings = $this->notificationTypeRepository
        ->storeOrUpdateUserNotification($request->toArray(), $request->auth->user_id);

        for ($i=0; $i<count($notificationSettings); $i++) {
            if ($notificationSettings[$i]['value']) {
                $settingStatus = config('constants.activity_log_actions.ACTIVATED');
            } else {
                $settingStatus = config('constants.activity_log_actions.DEACTIVATED');
            }
            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.NOTIFICATION_SETTING'),
                $settingStatus,
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                $request->toArray(),
                $request->auth->user_id,
                $notificationSettings[$i]['notification_type_id']
            ));
        }
        // Set response data
        $apiStatus = Response::HTTP_OK;
        $apiMessage =  trans('messages.success.MESSAGE_USER_NOTIFICATION_SETTINGS_UPDATED');

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
