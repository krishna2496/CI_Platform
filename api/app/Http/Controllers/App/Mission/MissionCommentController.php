<?php
namespace App\Http\Controllers\App\Mission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\MissionComment\MissionCommentRepository;
use App\Repositories\User\UserRepository;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use App\Helpers\Helpers;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Helpers\LanguageHelper;
use App\Helpers\TenantSettingHelper;
use App\Helpers\ExportCSV;
use App\Events\User\UserActivityLogEvent;
use Carbon\Carbon;
use App\Repositories\Notification\NotificationRepository;

//!  Mission comment controller
/*!
This controller is responsible for handling mission comment apply to mission and get volunteer list operations.
 */
class MissionCommentController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var ResponseHelper
     */
    private $responseHelper;

    /**
     * @var MissionCommentRepository
     */
    private $missionCommentRepository;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Helpers\TenantSettingHelper
     */
    private $tenantSettingHelper;

    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Repositories\Notification\NotificationRepository
     */
    private $notificationRepository;

    /**
     * Create a new comment controller instance
     *
     * @param App\Repositories\Mission\MissionCommentRepository $missionCommentRepository
     * @param App\Repositories\User\UserRepository $userRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param App\Helpers\Helpers
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @param  App\Helpers\TenantSettingHelper $tenantSettingHelper
     * @param App\Repositories\Notification\NotificationRepository $notificationRepository
     * @return void
     */
    public function __construct(
        MissionCommentRepository $missionCommentRepository,
        UserRepository $userRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        LanguageHelper $languageHelper,
        TenantSettingHelper $tenantSettingHelper,
        NotificationRepository $notificationRepository
    ) {
        $this->missionCommentRepository = $missionCommentRepository;
        $this->userRepository = $userRepository;
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->languageHelper = $languageHelper;
        $this->tenantSettingHelper = $tenantSettingHelper;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Get mission comments
     *
     * @param \Illuminate\Http\Request $request
     * @param int $missionId
     * @return Illuminate\Http\JsonResponse
     */
    public function getComments(Request $request, int $missionId): JsonResponse
    {
        try {
            $comments = $this->missionCommentRepository->getComments($missionId);
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            $defaultUserImage = $this->helpers->getUserDefaultProfileImage($tenantName);
            foreach ($comments as $key => $comment) {
                if ($comment->user == null) {
                    unset($comment->user);
                    $user = array(
                        "user_id" => null,
                        "first_name" => null,
                        "last_name" =>  null,
                        "avatar" => $defaultUserImage
                    );
                    $comments[$key]->user = $user;
                }
                $timezone = $this->userRepository->getUserTimezone($request->auth->user_id);
                $comment->created_at =  Carbon::parse($comment->created_at, config('constants.TIMEZONE'))
                ->setTimezone($timezone)->toDateTimeString();
            }
            $apiData = $comments;
            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($apiData->count() > 0) ? trans('messages.success.MESSAGE_MISSION_COMMENT_LISTING')
            : trans('messages.success.MESSAGE_NO_MISSION_COMMENT_FOUND');
            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_NOT_FOUND')
            );
        }
    }

    /**
     * Store mission comment
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validataions
        $validator = Validator::make(
            $request->all(),
            [
                "comment" => "required|max:600",
                "mission_id" => "required|integer|exists:mission,mission_id,deleted_at,NULL"
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_MISSION_COMMENT_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        // Need to check activated setting for comment approval status
        $isAutoApproved = $this->tenantActivatedSettingRepository->checkTenantSettingStatus(
            config('constants.tenant_settings.MISSION_COMMENT_AUTO_APPROVED'),
            $request
        );
        if ($isAutoApproved) {
            $request->request->add(
                [
                    'approval_status' => config('constants.comment_approval_status.PUBLISHED')
                ]
            );
        }
        $missionComment = $this->missionCommentRepository->store($request->auth->user_id, $request->toArray());

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiData = ['comment_id' => $missionComment->comment_id];
        $apiMessage = ($isAutoApproved) ? trans('messages.success.MESSAGE_AUTO_APPROVED_COMMENT_ADDED') :
        trans('messages.success.MESSAGE_COMMENT_ADDED');

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.MISSION'),
            config('constants.activity_log_actions.COMMENT_ADDED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $request->toArray(),
            $request->auth->user_id,
            $missionComment->comment_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Fetch user's comments on mission for dashboard
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function getUserMissionComments(Request $request): JsonResponse
    {
        $languageId = $this->languageHelper->getLanguageId($request);
		$defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
		$defaultTenantLanguageId = $defaultTenantLanguage->language_id;
        $userMissionComments = $this->missionCommentRepository->getUserComments(
            $request->auth->user_id,
            $languageId,
			$defaultTenantLanguageId,
            $this->tenantSettingHelper->getAvailableMissionTypes($request)
        );

        // Set response data
        $apiData = $userMissionComments;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = (count($apiData) > 0) ? trans('messages.success.MESSAGE_USER_COMMENTS_LISTING')
        : trans('messages.success.MESSAGE_NO_MISSION_COMMENTS_ENTRIES');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * User can delete comment from dashboard by comment id
     *
     *
     * @param Illuminate\Http\Request $request
     * @param  int  $commentId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $commentId): JsonResponse
    {
        try {
            $apiData = $this->missionCommentRepository->deleteUsersComment($commentId, $request->auth->user_id);
            $this->notificationRepository->deleteCommentNotifications($commentId);
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_COMMENT_DELETED');

            //Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MISSION_COMMENTS'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                [],
                $request->auth->user_id,
                $commentId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_COMMENT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_COMMENT_NOT_FOUND')
            );
        }
    }

    /**
     * User can export comments
     *
     * @param \Illuminate\Http\Request $request
     * @return Object
     */
    public function exportComments(Request $request): Object
    {
        $languageId = $this->languageHelper->getLanguageId($request);
		$defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
		$defaultTenantLanguageId = $defaultTenantLanguage->language_id;
        $userMissionComments = $this->missionCommentRepository->getUserComments(
            $request->auth->user_id,
            $languageId,
			$defaultTenantLanguageId,
            $this->tenantSettingHelper->getAvailableMissionTypes($request)
        );

        if (count($userMissionComments) == 0) {
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_NO_MISSION_COMMENTS_ENTRIES');
            return $this->responseHelper->success($apiStatus, $apiMessage);
        }

        $fileName = config('constants.export_mission_comment_file_names.MISSION_COMMENT_XLSX');
        $excel = new ExportCSV($fileName);
        $headings = [
            trans("general.export_mission_comment_headings.MISSION_TITLE"),
            trans("general.export_mission_comment_headings.COMMENT"),
            trans("general.export_mission_comment_headings.STATUS"),
            trans("general.export_mission_comment_headings.PUBLISHED_DATE"),
        ];

        $excel->setHeadlines($headings);
        foreach ($userMissionComments['comments'] as $comment) {
            $comment = $comment->toArray();
            $excel->appendRow([
                strip_tags(preg_replace('~[\r\n]+~', '', $comment['title'])),
                strip_tags(preg_replace('~[\r\n]+~', '', $comment['comment'])),
                $comment['approval_status'],
                $comment['created_at']
            ]);
        }

        $tenantName = $this->helpers->getSubDomainFromRequest($request);

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.MISSION_COMMENTS'),
            config('constants.activity_log_actions.EXPORT'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $userMissionComments['comments']->toArray(),
            null,
            $request->auth->user_id
        ));
        $path = $excel->export('app/'.$tenantName.'/MissionComments/'.$request->auth->user_id.'/exports');
        return response()->download($path, $fileName);
    }
}
