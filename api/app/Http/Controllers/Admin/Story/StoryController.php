<?php
namespace App\Http\Controllers\Admin\Story;

use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Repositories\Story\StoryRepository;
use App\Repositories\User\UserRepository;
use App\Traits\RestExceptionHandlerTrait;
use App\Transformations\StoryTransformable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Validator;
use App\Helpers\Helpers;
use App\Events\User\UserNotificationEvent;
use App\Events\User\UserActivityLogEvent;

//!  Story controller
/*!
This controller is responsible for handling story listing and update operations.
 */
class StoryController extends Controller
{
    use RestExceptionHandlerTrait, StoryTransformable;

    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Repositories\Story\StoryRepository
     */
    private $storyRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\User\UserRepository $userRepository
     * @param App\Repositories\Story\StoryRepository $storyRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        UserRepository $userRepository,
        StoryRepository $storyRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Helpers $helpers
    ) {
        $this->userRepository = $userRepository;
        $this->storyRepository = $storyRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->helpers = $helpers;
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $userId
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(int $userId, Request $request): JsonResponse
    {
        try {
            $user = $this->userRepository->find($userId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }

        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
        
        $userStories = $this->storyRepository->getUserStoriesWithPagination(
            $request,
            $defaultTenantLanguage->language_id,
            $userId
        );

        // get default user avatar
        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        $defaultAvatar = $this->helpers->getUserDefaultProfileImage($tenantName);

        $storyTransformed = $userStories
            ->getCollection()
            ->map(function ($story) use ($request, $defaultTenantLanguage, $defaultAvatar) {
                $story = $this->transformStory($story, $defaultTenantLanguage->language_id, $defaultAvatar);
                return $story;
            });

        $requestString = $request->except(['page', 'perPage']);
        $storyPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $storyTransformed,
            $userStories->total(),
            $userStories->perPage(),
            $userStories->currentPage(),
            [
                'path' => $request->url() . '?' . http_build_query($requestString),
                'query' => [
                    'page' => $userStories->currentPage(),
                ],
            ]
        );

        $apiData = $storyPaginated;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = ($apiData->count()) ?
        trans('messages.success.MESSAGE_STORIES_ENTRIES_LISTING') :
        trans('messages.success.MESSAGE_NO_STORIES_ENTRIES_FOUND');

        return $this->responseHelper->successWithPagination(
            $apiStatus,
            $apiMessage,
            $apiData,
            []
        );
    }

    /**
     * Publish/decline Story entry
     *
     * @param \Illuminate\Http\Request $request
     * @param int  $storyId
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $storyId): JsonResponse
    {
        try {
            // Server side validataions
            $validator = Validator::make(
                $request->all(),
                [
                    "status" => ['required', Rule::in(config('constants.story_status'))],
                ]
            );
            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_STORY_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }
            $storyDetails = $this->storyRepository->checkStoryExist($storyId);
            $this->storyRepository->updateStoryStatus($request->status, $storyId);

            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_STORY_STATUS_UPDATED');
            $apiData = ['story_id' => $storyId];

            // Make activity log
            $activityLogStatus = $request->status == 'PUBLISHED'
                ? config('constants.activity_log_actions.APPROVED')
                : config('constants.activity_log_actions.DECLINED');

            event(new UserActivityLogEvent(
                config('constants.activity_log_types.STORY'),
                $activityLogStatus,
                config('constants.activity_log_user_types.API'),
                $request->header('php-auth-user'),
                get_class($this),
                $request->toArray(),
                null,
                $storyId
            ));
            
            // Send notification to user
            $notificationType = config('constants.notification_type_keys.MY_STORIES');
            $entityId = $storyId;
            $action = config('constants.notification_actions.'.$request->status);
            $userId = $storyDetails->user_id;

            event(new UserNotificationEvent($notificationType, $entityId, $action, $userId));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STORY_NOT_FOUND')
            );
        }
    }
}
