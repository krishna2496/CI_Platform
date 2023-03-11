<?php

namespace App\Http\Controllers\Admin\Mission;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Repositories\MissionComment\MissionCommentRepository;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use App\Traits\RestExceptionHandlerTrait;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Mission\MissionRepository;
use Validator;
use Illuminate\Validation\Rule;
use App\Events\User\UserNotificationEvent;
use App\Events\User\UserActivityLogEvent;
use App\Repositories\Notification\NotificationRepository;

//!  Mission comment controller
/*!
This controller is responsible for handling mission comment listing, show, update and delete operations.
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
     * @var MissionRepository
     */
    private $missionRepository;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * @var App\Repositories\Notification\NotificationRepository
     */
    private $notificationRepository;

    /**
     * Create a new comment controller instance
     *
     * @param App\Repositories\Mission\MissionRepository $missionRepository
     * @param App\Repositories\Mission\MissionCommentRepository $missionCommentRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param Illuminate\Http\Request $request
     * @param App\Repositories\Notification\NotificationRepository $notificationRepository
     * @return void
     */
    public function __construct(
        MissionRepository $missionRepository,
        MissionCommentRepository $missionCommentRepository,
        ResponseHelper $responseHelper,
        NotificationRepository $notificationRepository,
        Request $request
    ) {
        $this->missionRepository = $missionRepository;
        $this->missionCommentRepository = $missionCommentRepository;
        $this->responseHelper = $responseHelper;
        $this->userApiKey = $request->header('php-auth-user');
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Get listing of tenant's comments
     *
     * @param int $missionId
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(int $missionId, Request $request): JsonResponse
    {
        try {
            $apiData = $this->missionCommentRepository->getComments(
                $missionId,
                config("constants.comment_approval_status"),
                $request
            );
            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($apiData->count()) ?
            trans('messages.success.MESSAGE_MISSION_COMMENT_LISTING') :
            trans('messages.success.MESSAGE_NO_MISSION_COMMENT_FOUND');
            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $apiData);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_NOT_FOUND')
            );
        }
    }

    /**
     * Get comment detail by mission id and comment id
     *
     * @param int $missionId
     * @param int  $commentId
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $missionId, int $commentId): JsonResponse
    {
        // First find mission
        try {
            $mission = $this->missionRepository->find($missionId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_NOT_FOUND')
            );
        }
        // Now find comments from that mission
        try {
            $apiData = $this->missionCommentRepository->getComment($commentId);
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_COMMENT_FOUND');
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData->toArray());
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_COMMENT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_COMMENT_NOT_FOUND')
            );
        }
    }

    /**
     * Update comment by mission id and comment id
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $missionId
     * @param  int  $commentId
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $missionId, int $commentId): JsonResponse
    {
        // First find mission
        try {
            $mission = $this->missionRepository->find($missionId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_NOT_FOUND')
            );
        }

        // Server side validation
        $validator = Validator::make(
            $request->all(),
            [
                "approval_status" => ['required',Rule::in(config('constants.comment_approval_status'))],
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_MISSION_DATA'),
                $validator->errors()->first()
            );
        }

        // Now find comments from that mission
        try {
            $comment = $this->missionCommentRepository->getComment($commentId);
            $data['approval_status'] = $request->approval_status;
            $apiData = $this->missionCommentRepository->updateComment($commentId, $data);
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_COMMENT_UPDATED');

            if (($comment->approval_status !== $request->approval_status) || (env('APP_ENV') === 'testing')) {
                // Send notification to user
                $notificationType = config('constants.notification_type_keys.MY_COMMENTS');
                $entityId = $commentId;
                $action = config('constants.notification_actions.'.$request->approval_status);
                $userId = $apiData->user_id;

                event(new UserNotificationEvent($notificationType, $entityId, $action, $userId));
            }

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MISSION_COMMENTS'),
                config('constants.activity_log_actions.COMMENT_UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $commentId
            ));
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData->toArray());
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_COMMENT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_COMMENT_NOT_FOUND')
            );
        }
    }

    /**
     * Delete comment by mission id and comment id
     *
     * @param  int  $missionId
     * @param  int  $commentId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $missionId, int $commentId): JsonResponse
    {
        // First find mission
        try {
            $this->missionRepository->find($missionId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_NOT_FOUND')
            );
        }

        // Get comment
        try {
            $this->missionCommentRepository->getCommentById($commentId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_COMMENT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_COMMENT_NOT_FOUND')
            );
        }

        $this->missionCommentRepository->deleteComment($commentId);
        $this->notificationRepository->deleteCommentNotifications($commentId);


        $apiStatus = Response::HTTP_NO_CONTENT;
        $apiMessage = trans('messages.success.MESSAGE_COMMENT_DELETED');

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.MISSION_COMMENTS'),
            config('constants.activity_log_actions.DELETED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            null,
            null,
            $commentId
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
