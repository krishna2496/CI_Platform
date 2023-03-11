<?php
namespace App\Http\Controllers\App\Mission;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\MissionInvite\MissionInviteRepository;
use App\Repositories\Notification\NotificationRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Helpers\ResponseHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use App\Repositories\User\UserRepository;
use App\Repositories\Mission\MissionRepository;
use Illuminate\Support\Facades\Mail;
use App\Jobs\AppMailerJob;
use App\Exceptions\TenantDomainNotFoundException;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Events\User\UserNotificationEvent;
use App\Events\User\UserActivityLogEvent;

//!  Mission invite controller
/*!
This controller is responsible for handling mission invite operation.
 */
class MissionInviteController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var MissionInviteRepository
     */
    private $missionInviteRepository;

    /**
     * @var NotificationRepository
     */
    private $notificationRepository;
    
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;
    
    /*
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var TenantOptionRepository
     */
    private $tenantOptionRepository;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * Create a new Mission controller instance.
     *
     * @param App\Repositories\Mission\MissionInviteRepository $missionInviteRepository
     * @param App\Repositories\Notification\NotificationRepository $notificationRepository
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @param App\Repositories\User\UserRepository $userRepository
     * @param App\Repositories\Mission\MissionRepository $missionRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param  Illuminate\Http\LanguageHelper $languageHelper
     * @param  App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(
        MissionInviteRepository $missionInviteRepository,
        NotificationRepository $notificationRepository,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        UserRepository $userRepository,
        MissionRepository $missionRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Helpers $helpers,
        TenantOptionRepository $tenantOptionRepository
    ) {
        $this->missionInviteRepository = $missionInviteRepository;
        $this->notificationRepository = $notificationRepository;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->userRepository = $userRepository;
        $this->missionRepository = $missionRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->helpers = $helpers;
        $this->tenantOptionRepository = $tenantOptionRepository;
    }
    
    /**
     * Invite to a mission
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function missionInvite(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                "mission_id" => "numeric|required|exists:mission,mission_id,deleted_at,NULL",
                "to_user_id" => "numeric|required|exists:user,user_id,deleted_at,NULL",
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_INVITE_MISSION_DATA'),
                $validator->errors()->first()
            );
        }
        
        // Check if user is already invited for this mission
        $getMissionInvite = $this->missionInviteRepository->getInviteMission(
            $request->mission_id,
            $request->to_user_id,
            $request->auth->user_id
        );
        if (!$getMissionInvite->isEmpty()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVITE_MISSION_ALREADY_EXIST'),
                trans('messages.custom_error_message.ERROR_INVITE_MISSION_ALREADY_EXIST')
            );
        }
        $inviteMission = $this->missionInviteRepository->inviteMission(
            $request->mission_id,
            $request->to_user_id,
            $request->auth->user_id
        );

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_INVITED_FOR_MISSION');
        $apiData = ['mission_invite_id' => $inviteMission->mission_invite_id];
        
        $emailNotificationInviteColleague = config('constants.tenant_settings.EMAIL_NOTIFICATION_INVITE_COLLEAGUE');

        $notificationTypeId = $this->notificationRepository
        ->getNotificationTypeID(config('constants.notification_type_keys.RECOMMENDED_MISSIONS'));

        // Check if to_user_id (colleague) has enabled notification for Recommended missions
        $notifyColleague = $this->notificationRepository
        ->userNotificationSetting($request->to_user_id, $notificationTypeId);

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.MISSION'),
            config('constants.activity_log_actions.INVITED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $request->toArray(),
            $request->auth->user_id,
            $request->mission_id
        ));

        if ($notifyColleague) {
            // Send notification to user
            $notificationType = config('constants.notification_type_keys.RECOMMENDED_MISSIONS');
            $entityId = $inviteMission->mission_invite_id;
            $action = config('constants.notification_actions.INVITE');
            $userId = $request->to_user_id;
            event(new UserNotificationEvent($notificationType, $entityId, $action, $userId));
        }

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
