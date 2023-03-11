<?php
namespace App\Http\Controllers\Admin\News;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\News\NewsRepository;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Validator;
use App\Models\News;
use App\Transformations\NewsTransformable;
use App\Helpers\LanguageHelper;
use App\Helpers\Helpers;
use App\Events\User\UserActivityLogEvent;
use App\Events\User\UserNotificationEvent;
use App\Repositories\Notification\NotificationRepository;

//!  News controller
/*!
This controller is responsible for handling news listing, show, store, update and delete operations.
 */
class NewsController extends Controller
{
    use RestExceptionHandlerTrait, NewsTransformable;
    /**
     * @var App\Repositories\News\NewsRepository
     */
    private $newsRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * @var App\Repositories\Notification\NotificationRepository
     */
    private $notificationRepository;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\News\NewsRepository $newsRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param App\Helpers\Helpers $helpers
     * @param \Illuminate\Http\Request $request
     * @param App\Repositories\Notification\NotificationRepository $notificationRepository
     * @return void
     */
    public function __construct(
        NewsRepository $newsRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Helpers $helpers,
        Request $request,
        NotificationRepository $notificationRepository
    ) {
        $this->newsRepository = $newsRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->helpers = $helpers;
        $this->userApiKey =$request->header('php-auth-user');
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Display listing of news
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
            $defaultTenantLanguageId = $defaultTenantLanguage->language_id;
            $defaultTenantLanguageCode = $defaultTenantLanguage->code;
            $languageId = $this->languageHelper->getLanguageId($request);
            $language = $this->languageHelper->getLanguageDetails($request);
            $languageCode = $language->code;

            $news = $this->newsRepository->getNewsList($request);
            $newsTransform = $news
            ->map(function (News $newsTransform) use (
                $languageId,
                $defaultTenantLanguageId,
                $languageCode,
                $defaultTenantLanguageCode
            ) {
                return $this->getTransformedNews(
                    $newsTransform,
                    true,
                    null,
                    $defaultTenantLanguageId,
                    $languageCode,
                    $defaultTenantLanguageCode
                );
            })->all();

            $requestString = $request->except(['page','perPage']);
            $newsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $newsTransform,
                $news->total(),
                $news->perPage(),
                $news->currentPage(),
                [
                    'path' => $request->url().'?'.http_build_query($requestString),
                    'query' => [
                        'page' => $news->currentPage()
                    ]
                ]
            );

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiData = $newsPaginated;
            $apiMessage = ($news->isEmpty()) ? trans('messages.custom_error_message.ERROR_NEWS_NOT_FOUND')
            : trans('messages.success.MESSAGE_NEWS_LISTING');

            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $apiData);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * Store a newly created news in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validations
        $validator = Validator::make(
            $request->all(),
            [
                "news_content" => "required",
                "news_content.translations" => "required_with:news_content",
                "news_content.translations.*.lang" => "required_with:news_content.translations|max:2",
                "news_content.translations.*.title" => "required_with:news_content.translations",
                "news_content.translations.*.description" =>
                "required_with:news_content.translations",
                "news_category_id" =>
                "required|exists:news_category,news_category_id,deleted_at,NULL",
                "status" => ['required', Rule::in(config('constants.news_status'))],
                "news_image" => "sometimes|required|url|valid_media_path",
                "user_thumbnail" => "url|valid_media_path",
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_NEWS_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        // Create a new record
        $news = $this->newsRepository->store($request);

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_NEWS_CREATED');
        $apiData = ['news_id' => $news->news_id];

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.NEWS'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            $news->news_id
        ));

        // Send notification to user
        $notificationType = config('constants.notification_type_keys.NEW_NEWS');
        $entityId = $news->news_id;
        $action = config('constants.notification_actions.CREATED');

        event(new UserNotificationEvent($notificationType, $entityId, $action));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update news details
     *
     * @param \Illuminate\Http\Request $request
     * @param int $newsId
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $newsId): JsonResponse
    {
        try {
            // Server side validations
            $validator = Validator::make(
                $request->all(),
                [
                    "news_content" => "sometimes|required",
                    "news_content.translations" => "required_with:news_content",
                    "news_content.translations.*.lang" =>
                    "required_with:news_content.translations|max:2",
                    "news_content.translations.*.title" => "required_with:news_content.translations",
                    "news_content.translations.*.description" => "required_with:news_content.translations",
                    "news_category_id" => "sometimes|required|exists:news_category,news_category_id,deleted_at,NULL",
                    "status" => ['sometimes', 'required', Rule::in(config('constants.news_status'))],
                    "news_image" => "sometimes|required|url|valid_media_path",
                    "user_thumbnail" => "url|valid_media_path",
                ]
            );

            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_NEWS_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            // Update news details
            $news = $this->newsRepository->update($request, $newsId);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_NEWS_UPDATED');
            $apiData = ['news_id' => $news->news_id];

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.NEWS'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $news->news_id
            ));


            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NEWS_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_NEWS_NOT_FOUND')
            );
        }
    }

    /**
     * Display the news details.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $newsId
     * @return Illuminate\Http\JsonResponse
     */
    public function show(Request $request, int $newsId): JsonResponse
    {
        try {
            $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
            $defaultTenantLanguageId = $defaultTenantLanguage->language_id;
            $defaultTenantLanguageCode = $defaultTenantLanguage->code;
            $languageId = $this->languageHelper->getLanguageId($request);
            $language = $this->languageHelper->getLanguageDetails($request);
            $languageCode = $language->code;
            // Get news details
            $news = $this->newsRepository->getNewsDetails($newsId);
            // Transform news details
            $newsTransform = $this->newsRepository
            ->getNewsDetails($newsId, config('constants.news_status.PUBLISHED'));
            // Transform news details
            $newsTransform = $this->getTransformedNews(
                $news,
                false,
                null,
                $defaultTenantLanguageId,
                $languageCode,
                $defaultTenantLanguageCode
            );

            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_NEWS_FOUND');

            return $this->responseHelper->success($apiStatus, $apiMessage, $newsTransform);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NEWS_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_NEWS_NOT_FOUND')
            );
        }
    }

    /**
     * Remove news details from storage.
     *
     * @param int $newsId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $newsId): JsonResponse
    {
        try {
            $this->newsRepository->delete($newsId);
            $this->notificationRepository->deleteNewsNotifications($newsId);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_NEWS_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.NEWS'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                [],
                null,
                $newsId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NEWS_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_NEWS_NOT_FOUND')
            );
        }
    }
}
