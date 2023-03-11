<?php
namespace App\Http\Controllers\Admin\Message;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Repositories\Message\MessageRepository;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Transformations\MessageTransformable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\User\UserNotificationEvent;
use App\Events\User\UserActivityLogEvent;
use App\Repositories\Notification\NotificationRepository;

//!  Message controller
/*!
This controller is responsible for handling message read, send, listing and delete operations.
 */
class MessageController extends Controller
{
    use RestExceptionHandlerTrait,MessageTransformable;
    /**
     * @var App\Repositories\Message\MessageRepository;
     */
    private $messageRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * @var App\Repositories\Notification\NotificationRepository
     */
    private $notificationRepository;

    /**
     * Create a new message controller instance
     *
     * @param App\Repositories\Message\MessageRepository;
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param \Illuminate\Http\Request $request
     * @param App\Repositories\Notification\NotificationRepository $notificationRepository
     * @return void
     */
    public function __construct(
        MessageRepository $messageRepository,
        ResponseHelper $responseHelper,
        Request $request,
        NotificationRepository $notificationRepository
    ) {
        $this->messageRepository = $messageRepository;
        $this->responseHelper = $responseHelper;
        $this->userApiKey =$request->header('php-auth-user');
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Send message to users
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->toArray(),
            [
                'subject' => 'required|max:255',
                'message' => 'required|max:60000',
                'admin' => 'string|max:255',
                'user_ids' =>'required|Array',
                'user_ids.*' =>'required|integer|distinct|min:1|exists:user,user_id,deleted_at,NULL',
            ]
        );
        
        // If validator fails
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_MESSAGE_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }
        
        // Store message data
        $messageIds = $this->messageRepository->store($request, config('constants.message.send_message_from.admin'));
        
        // Set response data
        $apiStatus = Response::HTTP_CREATED;

        $apiMessage = (count($request->user_ids) > 1) ?
            trans('messages.success.MESSAGE_USER_MESSAGES_SEND_SUCCESSFULLY') :
            trans('messages.success.MESSAGE_USER_MESSAGE_SEND_SUCCESSFULLY');
        $apiData = ['message_ids' => $messageIds];

        // Send notification to all users
        foreach ($messageIds as $message) {
            $notificationType = config('constants.notification_type_keys.NEW_MESSAGES');
            $entityId = $message['message_id'];
            $action = config('constants.notification_actions.CREATED');
            $userId = $message['user_id'];
            event(new UserNotificationEvent($notificationType, $entityId, $action, $userId));

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MESSAGE'),
                config('constants.activity_log_actions.CREATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $message['user_id']
            ));
        }
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Get admin messages data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserMessages(Request $request): JsonResponse
    {
        $userIds = !empty($request->get("users")) ? explode(',', $request->get("users")) : [];

        $userMessages = $this->messageRepository->getUserMessages(
            $request,
            config('constants.message.send_message_from.user'),
            $userIds
        );
        
        $messageTransformed = $this->transformMessage($userMessages, null, config('app.timezone'));

        $requestString = $request->except(['page','perPage']);
        $messagesPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $messageTransformed,
            $userMessages->total(),
            $userMessages->perPage(),
            $userMessages->currentPage(),
            [
                'path' => $request->url().'?'.http_build_query($requestString),
                'query' => [
                    'page' => $userMessages->currentPage()
                ]
            ]
        );
        
        // Set response data
        $apiData = $messagesPaginated;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = ($messagesPaginated->total() > 0) ?
            trans('messages.success.MESSAGE_MESSAGES_ENTRIES_LISTING') :
            trans('messages.success.MESSAGE_NO_MESSAGES_ENTRIES_FOUND');
        
        return $this->responseHelper->successWithPagination(
            $apiStatus,
            $apiMessage,
            $apiData
        );
    }
    
    /**
     * Remove Message details.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $messageId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $messageId): JsonResponse
    {
        try {
            $this->messageRepository->delete(
                $messageId,
                config('constants.message.send_message_from.user'),
                null
            );
            $this->notificationRepository->deleteMessageNotifications($messageId);
            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_USER_MESSAGE_DELETED');
            
            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MESSAGE'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $messageId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MESSAGE_USER_MESSAGE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MESSAGE_USER_MESSAGE_NOT_FOUND')
            );
        }
    }

    /**
     * Read message send by User.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $messageId
     * @return Illuminate\Http\JsonResponse
     */
    public function readMessage(Request $request, int $messageId): JsonResponse
    {
        try {
            $messageDetails = $this->messageRepository->readMessage(
                $messageId,
                null,
                config('constants.message.send_message_from.user')
            );
           
            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_READ_SUCCESSFULLY');
            $apiData = ['message_id' => $messageDetails->message_id];

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MESSAGE'),
                config('constants.activity_log_actions.READ'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $messageId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MESSAGE_USER_MESSAGE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MESSAGE_USER_MESSAGE_NOT_FOUND')
            );
        }
    }
}
