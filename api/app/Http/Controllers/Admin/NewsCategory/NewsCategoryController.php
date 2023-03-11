<?php
namespace App\Http\Controllers\Admin\NewsCategory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Repositories\NewsCategory\NewsCategoryRepository;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Validator;
use Illuminate\Validation\Rule;
use App\Events\User\UserActivityLogEvent;

//!  News category controller
/*!
This controller is responsible for handling news category listing, show, store, update and delete operations.
 */
class NewsCategoryController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\NewsCategory\NewsCategoryRepository
     */
    private $newsCategoryRepository;
    
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var string
     */
    private $userApiKey;
    
    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\NewsCategory\NewsCategoryRepository $newsCategoryRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        NewsCategoryRepository $newsCategoryRepository,
        ResponseHelper $responseHelper,
        Request $request
    ) {
        $this->newsCategoryRepository = $newsCategoryRepository;
        $this->responseHelper = $responseHelper;
        $this->userApiKey =$request->header('php-auth-user');
    }
    
    /**
     * Display news category lists.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $newsCategoryDetails = $this->newsCategoryRepository->getNewsCategoryList($request);
        
        // Set response data
        $apiStatus = Response::HTTP_OK;
        $apiMessage =
        ($newsCategoryDetails->isEmpty()) ? trans('messages.custom_error_message.ERROR_NEWS_CATEGORIES_NOT_FOUND')
        : trans('messages.success.MESSAGE_NEWS_CATEGORY_LISTING');
        
        return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $newsCategoryDetails);
    }

    /**
     * Store a newly created news category in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validations
        $validator = Validator::make(
            $request->all(),
            [
                "category_name" => "required|max:255|
                unique:news_category,category_name,NULL,news_category_id,deleted_at,NULL",
                "translations" => "required",
                "translations.*.lang" => "required_with:translations|max:2",
                "translations.*.title" => "required_with:translations"
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_NEWS_CATEGORY_INVALID_DATA'),
                $validator->errors()->first()
            );
        }
        
        // Create news category
        $newsCategory = $this->newsCategoryRepository->store($request->all());

        // Set response data
        $apiData = ['news_category_id' => $newsCategory->news_category_id];
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_NEWS_CATEGORY_CREATED');
        
        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.NEWS_CATEGORY'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            $newsCategory->news_category_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update news category in database.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $newsCategoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $newsCategoryId): JsonResponse
    {
        try {
            // Server side validations
            $validator = Validator::make(
                $request->all(),
                [
                    "category_name" => [
                        "max:255",
                        "sometimes",
                        "required",
                        Rule::unique('news_category')->ignore($newsCategoryId, 'news_category_id,deleted_at,NULL')
                    ],
                    "translations" => "sometimes|required",
                    "translations.*.lang" => "required_with:translations|max:2",
                    "translations.*.title" => "required_with:translations"
                ]
            );
            
            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_NEWS_CATEGORY_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }
         
            // Update news category
            $newsCategory = $this->newsCategoryRepository->update($request->toArray(), $newsCategoryId);

            // Set response data
            $apiData = ['news_category_id' => $newsCategory->news_category_id];
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_NEWS_CATEGORY_UPDATED');
            
            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.NEWS_CATEGORY'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $newsCategory->news_category_id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NEWS_CATEGORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_NEWS_CATEGORY_NOT_FOUND')
            );
        }
    }

    /**
     * Display news category details.
     *
     * @param int $newsCategoryId
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $newsCategoryId): JsonResponse
    {
        try {
            $newsCategoryDetail = $this->newsCategoryRepository->find($newsCategoryId);
                
            $apiData = $newsCategoryDetail->toArray();
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_NEWS_CATEGORY_FOUND');
            
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NEWS_CATEGORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_NEWS_CATEGORY_NOT_FOUND')
            );
        }
    }

    /**
     * Remove news category from database.
     *
     * @param int $newsCategoryId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $newsCategoryId): JsonResponse
    {
        try {
            $newsCategory = $this->newsCategoryRepository->delete($newsCategoryId);
            
            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_NEWS_CATEGORY_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.NEWS_CATEGORY'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                [],
                null,
                $newsCategoryId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NEWS_CATEGORY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_NEWS_CATEGORY_NOT_FOUND')
            );
        }
    }
}
