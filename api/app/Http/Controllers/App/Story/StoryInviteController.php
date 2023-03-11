<?php
namespace App\Http\Controllers\App\Story;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Repositories\StoryInvite\StoryInviteRepository;
use App\Repositories\Notification\NotificationRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\User\UserRepository;
use App\Helpers\ResponseHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use Illuminate\Support\Facades\Mail;
use App\Jobs\AppMailerJob;
use App\Exceptions\TenantDomainNotFoundException;
use App\Events\User\UserNotificationEvent;
use App\Events\User\UserActivityLogEvent;

//!  Story invite controller
/*!
This controller is responsible for handling story invite operations.
 */
class StoryInviteController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var StoryInviteRepository
     */
    private $storyInviteRepository;

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
     * Create a new Story controller instance.
     *
     * @param App\Repositories\Story\StoryInviteRepository $storyInviteRepository
     * @param App\Repositories\Notification\NotificationRepository $notificationRepository
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @param App\Repositories\User\UserRepository $userRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param  Illuminate\Http\LanguageHelper $languageHelper
     * @param  App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(
        StoryInviteRepository $storyInviteRepository,
        NotificationRepository $notificationRepository,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        UserRepository $userRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Helpers $helpers,
        TenantOptionRepository $tenantOptionRepository
    ) {
        $this->storyInviteRepository = $storyInviteRepository;
        $this->notificationRepository = $notificationRepository;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->userRepository = $userRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->helpers = $helpers;
        $this->tenantOptionRepository = $tenantOptionRepository;
    }
    
    /**
     * Invite to a story
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function storyInvite(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                "story_id" => "numeric|required|exists:story,story_id,deleted_at,NULL",
                "to_user_id" => "numeric|required|exists:user,user_id,deleted_at,NULL",
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_INVITE_STORY_DATA'),
                $validator->errors()->first()
            );
        }
        
        // Check if user is already invited for this story
        $getStoryInvite = $this->storyInviteRepository->getInviteStory(
            $request->story_id,
            $request->to_user_id,
            $request->auth->user_id
        );
        if (!$getStoryInvite->isEmpty()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVITE_STORY_ALREADY_EXIST'),
                trans('messages.custom_error_message.ERROR_INVITE_STORY_ALREADY_EXIST')
            );
        }
        $inviteStory = $this->storyInviteRepository->inviteStory(
            $request->story_id,
            $request->to_user_id,
            $request->auth->user_id
        );

        $notificationTypeId = $this->notificationRepository
        ->getNotificationTypeID(config('constants.notification_type_keys.RECOMMENDED_STORY'));
        
        // Check if to_user_id (colleague) has enabled notification for Recommended story
        $notifyColleague = $this->notificationRepository
        ->userNotificationSetting($request->to_user_id, $notificationTypeId);

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_INVITED_FOR_STORY');
        $apiData = ['story_invite_id' => $inviteStory->story_invite_id];

        //Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.STORY'),
            config('constants.activity_log_actions.INVITED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $request->toArray(),
            $request->auth->user_id,
            $inviteStory->story_invite_id
        ));

        if ($notifyColleague) {
            // Send notification to user
            $notificationType = config('constants.notification_type_keys.RECOMMENDED_STORY');
            $entityId = $inviteStory->story_invite_id;
            $action = config('constants.notification_actions.INVITE');
            $userId = $request->to_user_id;
            
            event(new UserNotificationEvent($notificationType, $entityId, $action, $userId));
        }

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
