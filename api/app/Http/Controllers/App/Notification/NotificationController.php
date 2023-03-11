<?php

namespace App\Http\Controllers\App\Notification;

use App\Repositories\NotificationType\NotificationTypeRepository;
use App\Repositories\Notification\NotificationRepository;
use App\Repositories\User\UserRepository;
use App\Traits\RestExceptionHandlerTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Helpers\LanguageHelper;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Validator;
use App\Services\NotificationService;
use App\Helpers\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\User\UserActivityLogEvent;

//!  Notification controller
/*!
This controller is responsible for handling notification listing, read/unread and clear all operations.
 */
class NotificationController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\NotificationType\NotificationTypeRepository
     */
    private $notificationTypeRepository;

    /**
     * @var App\Repositories\Notification\NotificationRepository
     */
    private $notificationRepository;

    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Service\NotificationService
     */
    private $notificationService;

    /**
     * @var App\Helpers\Helpers
     */
    public $helpers;

    /**
     * Create a new notification type controller instance.
     *
     * @param App\Repositories\NotificationType\NotificationTypeRepository $notificationTypeRepository
     * @param App\Repositories\Notification\NotificationRepository $notificationRepository
     * @param App\Repositories\User\UserRepository $userRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param App\Service\NotificationService $notificationService
     * @param App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(
        NotificationTypeRepository $notificationTypeRepository,
        UserRepository $userRepository,
        NotificationRepository $notificationRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        NotificationService $notificationService,
        Helpers $helpers
    ) {
        $this->notificationTypeRepository = $notificationTypeRepository;
        $this->userRepository = $userRepository;
        $this->notificationRepository = $notificationRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->notificationService = $notificationService;
        $this->helpers = $helpers;
    }

    /**
     * Fetch notification settings.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $languageId = $this->languageHelper->getLanguageId($request);
        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);

        //Fetch unread notification count
        $notificationsCount = $this->notificationRepository->getNotificationsCount($request->auth->user_id);
        $notificationData['unread_notifications'] = $notificationsCount;

        //Fetch notification
        $notifications = $this->notificationRepository->getNotifications($request->auth->user_id);
        
        foreach ($notifications as $notification) {
            $tenantName = $this->helpers->getSubDomainFromRequest($request);

            // This will create method name dynamically from notification type.
            // Example : notification type recommended_missions will convert into recommendedMissions()
            $notificaionType = str_replace("_", " ", $notification->notificationType->notification_type);
            $notificationString = str_replace(" ", "", ucwords($notificaionType));
            $methodName =  lcfirst($notificationString);
            $notificationDetails = $this->notificationService->$methodName(
                $notification,
                $tenantName,
                $languageId,
                $defaultTenantLanguage->language_id
            );
            $timezone = $this->userRepository->getUserTimezone($request->auth->user_id);
            if (!empty($notificationDetails)) {
				$notificationDetails['created_at'] =  Carbon::parse($notification->created_at, config('constants.TIMEZONE'))
				->setTimezone($timezone)->toDateTimeString();
				$notificationDetails['notification_id'] = $notification->notification_id;
				$notificationData['notifications'][] = $notificationDetails;
			}
        }

        // Set response data
        $apiData = $notificationData;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = (count($notifications) > 0) ? trans('messages.success.MESSAGE_NOTIFICATION_LISTING') :
        trans('messages.success.MESSAGE_NO_RECORD_FOUND');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }


    /**
     * Read/unread notification
     *
     * @param Illuminate\Http\Request $request
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function readUnreadNotification(Request $request, int $notificationId): JsonResponse
    {
        try {
            // do read unread notification
            $this->notificationRepository->readUnreadNotificationById(
                $notificationId,
                $request->auth->user_id
            );
       
            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_USER_NOTIFICATION_READ_UNREAD_SUCCESSFULLY');
            $apiData = ['notification_id' => $notificationId ];

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.NOTIFICATION'),
                config('constants.activity_log_actions.READ'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                $request->toArray(),
                $request->auth->user_id,
                $notificationId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOTIFICATION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MESSAGE_USER_NOTIFICATION_NOT_FOUND')
            );
        }
    }

    /**
     * Clear all notifications
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearAllNotifications(Request $request)
    {
        // Clear all notification
        $clearNotificationStatus = $this->notificationRepository->deleteAllNotifications($request->auth->user_id);

        // Set response data
        $apiStatus = Response::HTTP_NO_CONTENT;
        $apiMessage = trans('messages.success.MESSAGE_USER_NOTIFICATIONS_CLEAR_SUCCESSFULLY');

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.NOTIFICATION'),
            config('constants.activity_log_actions.CLEAR_ALL'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            null,
            $request->auth->user_id,
            null
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
