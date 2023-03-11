<?php
namespace App\Services;

use App\Repositories\MissionInvite\MissionInviteRepository;
use App\Repositories\StoryInvite\StoryInviteRepository;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\Timesheet\TimesheetRepository;
use App\Repositories\MissionComment\MissionCommentRepository;
use App\Repositories\Message\MessageRepository;
use App\Repositories\Story\StoryRepository;
use App\Repositories\MissionApplication\MissionApplicationRepository;
use App\Repositories\News\NewsRepository;
use App\Models\Notification;
use App\Helpers\Helpers;
use Carbon\Carbon;

class NotificationService
{
    /**
     * @var App\Repositories\MissionInvite\MissionInviteRepository
     */
    public $missionInviteRepository;

    /**
     * @var App\Repositories\StoryInvite\StoryInviteRepository
     */
    public $storyInviteRepository;

    /**
     * @var App\Repositories\Timesheet\TimesheetRepository
     */
    public $timesheetRepository;

    /**
     * @var App\Repositories\MissionComment\MissionCommentRepository
     */
    public $missionCommentRepository;

    /**
     * @var App\Repositories\Message\MessageCommentRepository
     */
    public $messageRepository;
    
    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    public $missionRepository;

    /**
     * @var App\Repositories\Story\StoryRepository
     */
    public $storyRepository;

    /**
     * @var App\Repositories\MissionApplication\MissionApplicationRepository
     */
    public $missionApplicationRepository;

    /**
     * @var App\Repositories\News\NewsRepository
     */
    public $newsRepository;

    /**
     * @var App\Helpers\Helpers
     */
    public $helpers;

    /**
     * Create a new Notification repository instance.
     *
     * @param  App\Repositories\MissionInvite\MissionInviteRepository $missionInviteRepository
     * @param  App\Repositories\StoryInvite\StoryInviteRepository $storyInviteRepository
     * @param  App\Repositories\Timesheet\TimesheetRepository $timesheetRepository
     * @param  App\Repositories\MissionComment\MissionCommentRepository $missionCommentRepository
     * @param  App\Repositories\Mission\MissionRepository $missionRepository
     * @param  App\Repositories\Message\MessageRepository $messageRepository
     * @param  App\Repositories\Story\StoryRepository $storyRepository
     * @param  App\Repositories\MissionApplication\MissionApplicationRepository $missionApplicationRepository
     * @param  App\Repositories\News\NewsRepository $newsRepository
     * @param  App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(
        MissionInviteRepository $missionInviteRepository,
        StoryInviteRepository $storyInviteRepository,
        TimesheetRepository $timesheetRepository,
        MissionCommentRepository $missionCommentRepository,
        MissionRepository $missionRepository,
        MessageRepository $messageRepository,
        StoryRepository $storyRepository,
        MissionApplicationRepository $missionApplicationRepository,
        NewsRepository $newsRepository,
        Helpers $helpers
    ) {
        $this->missionInviteRepository = $missionInviteRepository;
        $this->storyInviteRepository = $storyInviteRepository;
        $this->timesheetRepository = $timesheetRepository;
        $this->missionCommentRepository = $missionCommentRepository;
        $this->missionRepository = $missionRepository;
        $this->messageRepository = $messageRepository;
        $this->storyRepository = $storyRepository;
        $this->missionApplicationRepository = $missionApplicationRepository;
        $this->newsRepository = $newsRepository;
        $this->helpers = $helpers;
    }

    /**
     * Returns details for recommonded mission
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @param int $languageId
     * @param int $defaultTenantLanguageId
     * @return array
     */
    public function recommendedMissions(
        Notification $notification,
        string $tenantName = null,
        int $languageId,
        int $defaultTenantLanguageId
    ): array {
        // Get details
        $inviteDetails = $this->missionInviteRepository->getDetails($notification->entity_id);

        $missionName = $this->missionRepository->getMissionTitle(
            $inviteDetails->mission->mission_id,
            $languageId,
			$defaultTenantLanguageId
        );
        
        // Create message
        $response['icon'] = (is_null($inviteDetails->fromUser->avatar) || ($inviteDetails->fromUser->avatar === ""))
        ? $this->helpers->getUserDefaultProfileImage($tenantName) : $inviteDetails->fromUser->avatar;
        $response['is_avatar'] = 1;
        $response['notification_string'] = $inviteDetails->fromUser->first_name.
        " ".$inviteDetails->fromUser->last_name." - "
        .trans('general.notification.RECOMMENDS_THIS_MISSION')." - ".$missionName;
        $response['is_read'] = $notification->is_read;
        $response['link'] = '/mission-detail/'.$inviteDetails->mission->mission_id;
        return $response;
    }

    /**
     * Returns details for recommonded story
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @param int $languageId
     * @param int $defaultTenantLanguageId
     * @return array
     */
    public function recommendedStory(
        Notification $notification,
        string $tenantName = null,
        int $languageId,
        int $defaultTenantLanguageId
    ): array {
        
        // Get details
        $inviteDetails = $this->storyInviteRepository->getDetails($notification->entity_id);
        $storyTitle = $inviteDetails->story->title;

        // Create message
        $response['icon'] = (is_null($inviteDetails->fromUser->avatar) || ($inviteDetails->fromUser->avatar === ""))
        ? $this->helpers->getUserDefaultProfileImage($tenantName) : $inviteDetails->fromUser->avatar;
        $response['is_avatar'] = 1;
        $response['notification_string'] = $inviteDetails->fromUser->first_name.
        " ".$inviteDetails->fromUser->last_name." - "
        .trans('general.notification.RECOMMENDS_THIS_STORY')." - ".$storyTitle;
        $response['is_read'] = $notification->is_read;
        $response['link'] = '/story-detail/'.$inviteDetails->story->story_id;
        return $response;
    }
    
    /**
     * Returns details for volunteering hours
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @return array
     */
    public function volunteeringHours(Notification $notification, string $tenantName): array
    {
        // Get details
        $timesheetDetails = $this->timesheetRepository->getDetailOfTimesheetEntry($notification->entity_id);
        $formattedDate = Carbon::createFromFormat('m-d-Y', $timesheetDetails->date_volunteered);
        $date = Carbon::parse($formattedDate)->format('d/m/Y');
        $status = trans('general.notification_status.'.$notification->action);

        // Create message
        $icon = ($notification->action === config('constants.notification_status.APPROVED')
        || $notification->action === config('constants.notification_status.AUTOMATICALLY_APPROVED')) ?
        Config('constants.notification_icons.APPROVED') : Config('constants.notification_icons.DECLINED');
        
        $response['icon'] =  $this->helpers->getAssetsUrl($tenantName).$icon;
        $response['notification_string'] = trans('general.notification.VOLUNTEERING_HOURS_SUBMITTED_THE')." ".
        $date." ".trans('general.notification.IS')." ".$status;
        $response['is_read'] = $notification->is_read;
        $response['link'] = '/volunteering-timesheet';
        return $response;
    }

    /**
     * Returns details for volunteering goals
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @return array
     */
    public function volunteeringGoals(Notification $notification, string $tenantName): array
    {
        // Get details
        $timesheetDetails = $this->timesheetRepository->getDetailOfTimesheetEntry($notification->entity_id);
        $formattedDate = Carbon::createFromFormat('m-d-Y', $timesheetDetails->date_volunteered);
        $date = Carbon::parse($formattedDate)->format('d/m/Y');
        $status = trans('general.notification_status.'.$notification->action);

        // Create message
        $icon = ($notification->action === config('constants.notification_status.APPROVED')
        || $notification->action === config('constants.notification_status.AUTOMATICALLY_APPROVED')) ?
        Config('constants.notification_icons.APPROVED') : Config('constants.notification_icons.DECLINED');
      
        $response['icon'] = $this->helpers->getAssetsUrl($tenantName).$icon;
        $response['notification_string'] = trans('general.notification.VOLUNTEERING_GOALS_SUBMITTED_THE')." "
        .$date." ".trans('general.notification.IS')." ".$status;
        $response['is_read'] = $notification->is_read;
        $response['link'] = '/volunteering-timesheet';
        return $response;
    }
    
    /**
     * Returns details for my comments
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @return array
     */
    public function myComments(Notification $notification, string $tenantName): array
    {
        // Get details
        $commentDetails = $this->missionCommentRepository->getCommentDetail($notification->entity_id);
        $date = Carbon::parse($commentDetails->created_at)
        ->setTimezone(config('constants.TIMEZONE'))->format(config('constants.FRONT_DATE_FORMAT'));
        $status = trans('general.notification_status.'.$notification->action);

        // Create message
        $icon = ($notification->action === config('constants.notification_status.PUBLISHED')) ?
        Config('constants.notification_icons.APPROVED') : Config('constants.notification_icons.DECLINED');
        
        $response['icon'] = $this->helpers->getAssetsUrl($tenantName).$icon;
        $response['notification_string'] = trans('general.notification.COMMENT_OF')." "
        .$date." ".trans('general.notification.IS')." ".$status;
        $response['is_read'] = $notification->is_read;
        $response['link'] = '/comment-history';
        return $response;
    }

    /**
     * Returns details for my stories
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @return array
     */
    public function myStories(Notification $notification, string $tenantName): array
    {
        // Get details
        $storyDetails = $this->storyRepository->getStoryDetail($notification->entity_id);

        $date = Carbon::parse($storyDetails[0]['created_at'])
        ->setTimezone(config('constants.TIMEZONE'))->format(config('constants.FRONT_DATE_FORMAT'));
        $status = trans('general.notification_status.'.$notification->action);

        // Create message
        $icon = ($notification->action === config('constants.notification_status.PUBLISHED')) ?
        Config('constants.notification_icons.APPROVED') : Config('constants.notification_icons.DECLINED');
        $response['icon'] = $this->helpers->getAssetsUrl($tenantName).$icon;
        $response['notification_string'] = trans('general.notification.STORY')." "
        .trans('general.notification.IS')." ".$status." - ".$storyDetails[0]['title'];
        $response['is_read'] = $notification->is_read;
        $response['link'] = ($notification->action !==
        config('constants.story_status.DECLINED'))
        ? '/story-detail/'.$notification->entity_id : '/my-stories';
        return $response;
    }

    /**
     * Returns details for new message
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @return array
     */
    public function newMessages(Notification $notification, string $tenantName): array
    {
        // Get details
        $messageDetails = $this->messageRepository->getMessageDetail($notification->entity_id);
        
        // Create message
        $response['icon'] = $this->helpers->getAssetsUrl($tenantName).Config('constants.notification_icons.NEW');
        $response['notification_string'] = trans('general.notification.NEW_MESSAGE')." - ".$messageDetails->subject;
        $response['is_read'] = $notification->is_read;
        $response['link'] = '/messages';
        return $response;
    }

    /**
     * Returns details for new mission
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @param int $languageId
     * @param int $defaultTenantLanguageId
     * @return array
     */
    public function newMissions(
        Notification $notification,
        string $tenantName = null,
        int $languageId,
        int $defaultTenantLanguageId
    ): array {
        // Get details
        $missionName = $this->missionRepository->getMissionTitle(
            $notification->entity_id,
            $languageId,
			$defaultTenantLanguageId
        );

        // Create message
        $response['icon'] = $this->helpers->getAssetsUrl($tenantName).Config('constants.notification_icons.NEW');
        $response['notification_string'] = trans('general.notification.NEW_MISSION')." - ".$missionName;
        $response['is_read'] = $notification->is_read;
        $response['link'] = '/mission-detail/'.$notification->entity_id;
        return $response;
    }

    /**
     * Returns details for new news
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @param int $languageId
     * @param int $defaultTenantLanguageId
     * @return array
     */
    public function newNews(
        Notification $notification,
        string $tenantName = null,
        int $languageId,
        int $defaultTenantLanguageId
    ): array {
        // Get details
        $newsTitle = $this->newsRepository->getNewsTitle(
            $notification->entity_id,
            $languageId,
            $defaultTenantLanguageId
        );

        // Create message
        $response['icon'] = $this->helpers->getAssetsUrl($tenantName).Config('constants.notification_icons.NEW');
        $response['notification_string'] = trans('general.notification.NEW_NEWS')." - ".$newsTitle;
        $response['is_read'] = $notification->is_read;
        $response['link'] = '/news-detail/'.$notification->entity_id;
        return $response;
    }

    /**
     * Returns details for mission application
     *
     * @param App\Models\Notification $notification
     * @param string $tenantName
     * @param int $languageId
     * @param int $defaultTenantLanguageId
     * @return array
     */
    public function missionApplication(
        Notification $notification,
        string $tenantName = null,
        int $languageId,
        int $defaultTenantLanguageId
    ): array {
        // Get details
        $missionId = $this->missionApplicationRepository->getMissionId($notification->entity_id);

        $missionName = $this->missionRepository->getMissionTitle(
            $missionId,
            $languageId,
			$defaultTenantLanguageId
        );
        $status = trans('general.notification_status.'.$notification->action);
        
        // Create message
        $icon = ($notification->action === config('constants.notification_status.AUTOMATICALLY_APPROVED')) ?
        Config('constants.notification_icons.APPROVED') : Config('constants.notification_icons.DECLINED');
        $response['icon'] = $this->helpers->getAssetsUrl($tenantName).$icon;

        $response['notification_string'] = trans('general.notification.VOLUNTEERING_REQUEST')." ".$status." ".
        trans('general.notification.FOR_THIS_MISSION')." ".$missionName;
        $response['is_read'] = $notification->is_read;
        $response['link'] = '/mission-detail/'.$missionId;
        return $response;
    }
}
