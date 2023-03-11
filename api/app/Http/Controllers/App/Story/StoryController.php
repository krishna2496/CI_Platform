<?php
namespace App\Http\Controllers\App\Story;

use App\Events\Story\StoryDeletedEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\Story\StoryRepository;
use App\Repositories\StoryVisitor\StoryVisitorRepository;
use App\Models\Story;
use App\Helpers\ResponseHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\TenantSettingHelper;
use App\Http\Controllers\Controller;
use App\Helpers\ExportCSV;
use Illuminate\Http\JsonResponse;
use App\Helpers\Helpers;
use App\Traits\RestExceptionHandlerTrait;
use App\Transformations\StoryTransformable;
use Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\User\UserActivityLogEvent;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\Notification\NotificationRepository;

//!  Story controller
/*!
This controller is responsible for handling story store, update, delete, copy, delete story image,
user story listing, published story listing and export operations.
 */
class StoryController extends Controller
{
    use RestExceptionHandlerTrait,StoryTransformable;
    /**
     * @var App\Repositories\Story\StoryRepository
     */
    private $storyRepository;

    /**
     * @var App\Repositories\StoryVisitor\StoryVisitorRepository
     */
    private $storyVisitorRepository;

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
     * @var App\Helpers\TenantSettingHelper
     */
    private $tenantSettingHelper;

    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    private $missionRepository;

    /**
     * @var App\Repositories\Notification\NotificationRepository
     */
    private $notificationRepository;

    /**
     * Create a new Story controller instance
     *
     * @param App\Repositories\Story\StoryRepository $storyRepository
     * @param App\Repositories\StoryVisitor\StoryVisitorRepository $storyVisitorRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\Helpers $helpers
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param App\Helpers\TenantSettingHelper $tenantSettingHelper
     * @param App\Repositories\Mission\MissionRepository $missionRepository
     * @param App\Repositories\Notification\NotificationRepository $notificationRepository
     * @return void
     */
    public function __construct(
        StoryRepository $storyRepository,
        StoryVisitorRepository $storyVisitorRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers,
        LanguageHelper $languageHelper,
        TenantSettingHelper $tenantSettingHelper,
        MissionRepository $missionRepository,
        NotificationRepository $notificationRepository
    ) {
        $this->storyRepository = $storyRepository;
        $this->storyVisitorRepository = $storyVisitorRepository;
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
        $this->languageHelper = $languageHelper;
        $this->tenantSettingHelper = $tenantSettingHelper;
        $this->missionRepository = $missionRepository;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Store story details
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->toArray(),
            [
                'mission_id' =>
                'required|exists:mission,mission_id,deleted_at,NULL,publication_status,'.
                config("constants.publication_status.APPROVED"),
                'title' => 'required|max:255',
                'story_images' => 'max:'.config("constants.STORY_MAX_IMAGE_LIMIT"),
                'story_images.*' => 'valid_story_image_type|max:'.config("constants.STORY_IMAGE_SIZE_LIMIT"),
                'story_videos' => 'valid_story_video_url|max_video_url|sometimes|required',
                'description' => 'required|max:40000'
            ]
        );

        // If validator fails
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_STORY_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        try {
            $missionApplicationStatus = $this->missionRepository->getLatestMissionApplicationStatus(
                (int)$request->get('mission_id'),
                $request->auth->user_id
            );

            if ($missionApplicationStatus !== config('constants.application_status.AUTOMATICALLY_APPROVED')) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_SUBMIT_STORY_INVALID'),
                    trans('messages.custom_error_message.ERROR_STORY_MISSION_APPLICATION_NOT_APPROVED')
                );
            }
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_SUBMIT_STORY_INVALID'),
                trans('messages.custom_error_message.ERROR_STORY_MISSION_APPLICATION_NOT_FOUND')
            );
        }

        // Store story data
        $storyData = $this->storyRepository->store($request);

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.STORY_ADDED_SUCCESSFULLY');
        $apiData = ['story_id' => $storyData->story_id];

        // get the story media data for log
        $requestData = $request->toArray();

        if ($request->hasFile('story_images')) {
            $storyImageArray = array();
            $storyMedia = $this->storyRepository->getStoryMedia($storyData->story_id);

            foreach ($storyMedia as $mediaData) {
                // found the image
                if ($mediaData->type == 'image') {
                    array_push($storyImageArray, $mediaData->path);
                }
            }
            $requestData ['story_images'] = $storyImageArray;
        }

        //Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.STORY'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $requestData,
            $request->auth->user_id,
            $storyData->story_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update story details
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $storyId
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $storyId): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->toArray(),
                [
                    'mission_id' =>
                    'sometimes|required|exists:mission,mission_id,deleted_at,NULL,publication_status,'.
                    config("constants.publication_status.APPROVED"),
                    'title' => 'sometimes|required|max:255',
                    'story_images' => 'max:'.config("constants.STORY_MAX_IMAGE_LIMIT"),
                    'story_images.*' => 'valid_story_image_type|max:'.config("constants.STORY_IMAGE_SIZE_LIMIT"),
                    'story_videos' => 'valid_story_video_url|max_video_url',
                    'description' => 'sometimes|required|max:40000'
                ]
            );

            // If validator fails
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_STORY_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            $storyStatus = array(config('constants.story_status.PUBLISHED'),
            config('constants.story_status.DECLINED'));

            // Check if approved or declined story
            $validStoryStatus = $this->storyRepository->checkStoryStatus(
                $request->auth->user_id,
                $storyId,
                $storyStatus
            );

            if (!$validStoryStatus) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_STORY_PUBLISHED_OR_DECLINED'),
                    trans('messages.custom_error_message.ERROR_STORY_PUBLISHED_OR_DECLINED')
                );
            }

            // Update story data
            $storyData = $this->storyRepository->update($request, $storyId);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_STORY_UPDATED');
            $apiData = ['story_id' => $storyData->story_id];

            // get the story media data for log
            $requestData = $request->toArray();

            if ($request->hasFile('story_images')) {
                $storyImageArray = array();
                $storyMedia = $this->storyRepository->getStoryMedia($storyData->story_id);

                foreach ($storyMedia as $mediaData) {
                    // found the image
                    if ($mediaData->type == 'image') {
                        array_push($storyImageArray, $mediaData->path);
                    }
                }
                $requestData ['story_images'] = $storyImageArray;
            }

            //Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.STORY'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                $requestData,
                $request->auth->user_id,
                $storyData->story_id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STORY_NOT_FOUND')
            );
        }
    }

    /**
     * Remove story details.
     *
     * @param \Illuminate\Http\Request $request
     * @param int  $storyId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $storyId): JsonResponse
    {
        try {
            $this->storyRepository->delete($storyId, $request->auth->user_id);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_STORY_DELETED');

            //Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.STORY'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                [],
                $request->auth->user_id,
                $storyId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STORY_NOT_FOUND')
            );
        }
    }

    /**
     * Get story details.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $storyId
     * @return Illuminate\Http\JsonResponse
     */
    public function show(Request $request, int $storyId): JsonResponse
    {
        $language = $this->languageHelper->getLanguageDetails($request);
        $languageId = $language->language_id;

        // Get Story details
        $story = $this->storyRepository
            ->getStoryDetails(
                $storyId,
                config('constants.story_status.PUBLISHED'),
                $request->auth->user_id,
                array(config('constants.story_status.DRAFT'), config('constants.story_status.PENDING')),
                $this->tenantSettingHelper->getAvailableMissionTypes($request)
            );

        if ($story->count() == 0) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STORY_NOT_FOUND')
            );
        }

        // conditions for story view count manage
        $storyArray = array('story_id' => $story[0]->story_id,
                            'story_user_id' => $story[0]->user_id,
                            'status' => $story[0]->status);

        $storyViewCount = $this->storyVisitorRepository->updateStoryViewCount($storyArray, $request->auth->user_id);

        // not found same story user & login user & story status is published then story visitor count is updated
        // so make the activiy log
        if ($storyArray['story_user_id'] != $request->auth->user_id
            && $storyArray['status'] ==  config('constants.story_status.PUBLISHED')) {
                // get story visitor data to get object id
            $storyVisitorData = $this->storyVisitorRepository->getStoryVisitorData(
                $storyArray['story_id'],
                $request->auth->user_id
            );

            //Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.STORY_VISITOR'),
                config('constants.activity_log_actions.COUNTED'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                $storyArray,
                $request->auth->user_id,
                $storyVisitorData->story_visitor_id
            ));
        }

        // get default user avatar
        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        $defaultAvatar = $this->helpers->getUserDefaultProfileImage($tenantName);

        // Transform story details
        $storyTransformedData = $this->transformStoryDetails(
            $story[0],
            $storyViewCount,
            $defaultAvatar,
            $languageId
        );

        // Check mission status
        $missionStatus = $this->missionRepository->checkMissionStatus($storyTransformedData['mission_id']);
        $storyTransformedData['open_mission_button'] = ($missionStatus) ? "1" : "0";

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_STORY_FOUND');

        return $this->responseHelper->success($apiStatus, $apiMessage, $storyTransformedData);
    }

    /**
     * User can copy story if its declined
     *
     * @param \Illuminate\Http\Request $request
     * @param int $oldStoryId
     * @return Illuminate\Http\JsonResponse
     */
    public function copyStory(Request $request, int $oldStoryId): JsonResponse
    {
        try {
            //check for story exist?
            $storyData = $this->storyRepository->findStoryByUserId($request->auth->user_id, $oldStoryId);

            $storyStatus = array(
                config('constants.story_status.DECLINED')
            );

            // User can't submit story if its published or declined
            $notDeclinedStory = $this->storyRepository->checkStoryStatus(
                $request->auth->user_id,
                $oldStoryId,
                $storyStatus
            );

            if ($notDeclinedStory) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_COPY_DECLINED_STORY'),
                    trans('messages.custom_error_message.ERROR_COPY_DECLINED_STORY')
                );
            }
            $newStoryId = $this->storyRepository->createStoryCopy($oldStoryId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STORY_NOT_FOUND')
            );
        }

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_STORY_COPIED_SUCCESS');
        $apiData = ['story_id' => $newStoryId ];

        //Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.STORY'),
            config('constants.activity_log_actions.COPIED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            [],
            $request->auth->user_id,
            $newStoryId
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }


    /**
     * Export user's story
     *
     * @param \Illuminate\Http\Request $request
     * @return Object
     */
    public function exportStories(Request $request): Object
    {
        //get login user story data
        $language = $this->languageHelper->getLanguageDetails($request);
        $stories = $this->storyRepository->getUserStories(
            $language->language_id,
            $request->auth->user_id,
            $this->tenantSettingHelper->getAvailableMissionTypes($request)
        );

        if ($stories->count() == 0) {
            $apiStatus = Response::HTTP_OK;
            $apiMessage =  trans('messages.success.MESSAGE_UNABLE_TO_EXPORT_USER_STORIES_ENTRIES');
            return $this->responseHelper->success($apiStatus, $apiMessage);
        }

        $fileName = config('constants.export_story_file_names.STORY_XLSX');
        $excel = new ExportCSV($fileName);
        $headings = [
            trans("general.export_story_headings.STORY_TITLE"),
            trans("general.export_story_headings.STORY_DESCRIPTION"),
            trans("general.export_story_headings.STORY_STATUS"),
            trans("general.export_story_headings.MISSION_TITLE"),
            trans("general.export_story_headings.CREATED_DATE"),
            trans("general.export_story_headings.PUBLISHED_DATE")
        ];

        $defaultLanguage = $this->languageHelper->getDefaultTenantLanguage($request);

        $excel->setHeadlines($headings);
        foreach ($stories as $story) {

            if (!isset($story->mission->missionLanguage[0])) {
                $storyDefaultLanguage = $this->storyRepository->getUserStories(
                    $defaultLanguage->language_id,
                    $request->auth->user_id,
                    $this->tenantSettingHelper->getAvailableMissionTypes($request),
                    $story->story_id
                );

                $story = $storyDefaultLanguage[0];
            }

            $excel->appendRow([
                strip_tags(preg_replace('~[\r\n]+~', '', $story->title)),
                strip_tags(preg_replace('~[\r\n]+~', '', $story->description)),
                $story->status,
                strip_tags(preg_replace('~[\r\n]+~', '', $story->mission->missionLanguage[0]->title)),
                $story->created_at,
                $story->published_at
            ]);
        }

        $tenantName = $this->helpers->getSubDomainFromRequest($request);

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.STORY'),
            config('constants.activity_log_actions.EXPORT'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $stories->toArray(),
            null,
            $request->auth->user_id
        ));
        $path = $excel->export('app/'.$tenantName.'/story/'.$request->auth->user_id.'/exports');
        return response()->download($path, $fileName);
    }

    /**
     * Submit story for admin approval
     *
     * @param \Illuminate\Http\Request $request
     * @param int $storyId
     * @return Illuminate\Http\JsonResponse
     */
    public function submitStory(Request $request, int $storyId): JsonResponse
    {
        try {
            $storyStatus = array(
                config('constants.story_status.PUBLISHED'),
                config('constants.story_status.DECLINED')
            );

            // User can't submit story if its published or declined
            $validStoryStatus = $this->storyRepository->checkStoryStatus(
                $request->auth->user_id,
                $storyId,
                $storyStatus
            );

            if (!$validStoryStatus) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_SUBMIT_STORY_PUBLISHED_OR_DECLINED'),
                    trans('messages.custom_error_message.ERROR_SUBMIT_STORY_PUBLISHED_OR_DECLINED')
                );
            }

            // Submit story
            $storyData = $this->storyRepository->submitStory($request->auth->user_id, $storyId);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_STORY_SUBMITTED_SUCCESSFULLY');
            $apiData = ['story_id' => $storyData->story_id];

            //Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.STORY'),
                config('constants.activity_log_actions.SUBMIT_FOR_APPROVAL'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                [],
                $request->auth->user_id,
                $storyId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STORY_NOT_FOUND')
            );
        }
    }

    /**
     * Delete story image.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $storyId
     * @param int $mediaId
     * @return Illuminate\Http\JsonResponse
     */
    public function deleteStoryImage(Request $request, int $storyId, int $mediaId): JsonResponse
    {
        try {
            // Fetch story data
            $storyData = $this->storyRepository->findStoryByUserId($request->auth->user_id, $storyId);

            $statusArray = [
                config('constants.story_status.PUBLISHED'),
                config('constants.story_status.DECLINED')
            ];

            // User cannot remove story image if story is published or declined
            if (in_array($storyData->status, $statusArray)) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_STORY_IMAGE_DELETE'),
                    trans('messages.custom_error_message.ERROR_STORY_IMAGE_DELETE')
                );
            }

            // Delete story image
            try {
                $storyImage = $this->storyRepository->deleteStoryImage($mediaId, $storyId);
            } catch (ModelNotFoundException $e) {
                return $this->modelNotFound(
                    config('constants.error_codes.ERROR_STORY_IMAGE_NOT_FOUND'),
                    trans('messages.custom_error_message.ERROR_STORY_IMAGE_NOT_FOUND')
                );
            }

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_STORY_IMAGE_DELETED');

            //Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.STORY_IMAGE'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                [],
                $request->auth->user_id,
                $mediaId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STORY_NOT_FOUND')
            );
        }
    }

    /**
     * Used for get login user's all stories data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserStories(Request $request): JsonResponse
    {
        // get user's all story data
        $language = $this->languageHelper->getLanguageDetails($request);

        $userStories = $this->storyRepository->getUserStoriesWithPagination(
            $request,
            $language->language_id,
            $request->auth->user_id,
            null,
            $this->tenantSettingHelper->getAvailableMissionTypes($request)
        );

        // Get the story status count
        $storyStatusCounts = $this->storyRepository->getUserStoriesStatusCounts(
            $request->auth->user_id,
            $this->tenantSettingHelper->getAvailableMissionTypes($request)
        );

        $storyTransformedData = $this->transformUserStories($userStories, $storyStatusCounts);

        $requestString = $request->except(['page','perPage']);
        $storyPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $storyTransformedData,
            $userStories->total(),
            $userStories->perPage(),
            $userStories->currentPage(),
            [
                'path' => $request->url().'?'.http_build_query($requestString),
                'query' => [
                    'page' => $userStories->currentPage()
                ]
            ]
        );

        $apiData = $storyPaginated;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = ($apiData->total() > 0) ?
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
     * Story listing on front end
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function publishedStories(Request $request): JsonResponse
    {
        // get user's all story data
        $language = $this->languageHelper->getLanguageDetails($request);

        // get all published stories of users
        $publishedStories = $this->storyRepository->getUserStoriesWithPagination(
            $request,
            $language->language_id,
            null,
            config('constants.story_status.PUBLISHED'),
            $this->tenantSettingHelper->getAvailableMissionTypes($request)
        );

        // get default avatar
        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        $defaultAvatar = $this->helpers->getUserDefaultProfileImage($tenantName);

        $storyTransformedData = $this->transformPublishedStory($publishedStories, $defaultAvatar);
        $requestString = $request->except(['page','perPage']);
        $storyPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $storyTransformedData,
            $publishedStories->total(),
            $publishedStories->perPage(),
            $publishedStories->currentPage(),
            [
                'path' => $request->url().'?'.http_build_query($requestString),
                'query' => [
                    'page' => $publishedStories->currentPage()
                ]
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
     * Fetch edit story details.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $storyId
     * @return Illuminate\Http\JsonResponse
     */
    public function editStory(Request $request, int $storyId): JsonResponse
    {
        try {
            // Fetch story details
            $storyData = $this->storyRepository->findStoryByUserId($request->auth->user_id, $storyId);

            $statusArray = [
                config('constants.story_status.DRAFT'),
                config('constants.story_status.PENDING')
            ];
            // User cannot edit story if story is published or declined
            if (!in_array($storyData->status, $statusArray)) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_STORY_PUBLISHED_OR_DECLINED'),
                    trans('messages.custom_error_message.ERROR_STORY_PUBLISHED_OR_DECLINED')
                );
            }

            // Fetch edit story details
            $story = $this->storyRepository
            ->getStoryDetails($storyData->story_id);

            $apiStatus = Response::HTTP_OK;
            $apiData = $story->toArray();
            $apiMessage = trans('messages.success.MESSAGE_STORY_FOUND');

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STORY_NOT_FOUND')
            );
        }
    }
}
