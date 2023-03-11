<?php
namespace App\Http\Controllers\Admin\PolicyPage;

use App\Http\Controllers\Controller;
use App\Repositories\PolicyPage\PolicyPageRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Illuminate\Validation\Rule;
use App\Events\User\UserActivityLogEvent;

//!  Policypage controller
/*!
This controller is responsible for handling policypage listing, show, store, update and delete operations.
 */
class PolicyPageController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\PolicyPage\PolicyPageRepository
     */
    private $policyPageRepository;
    
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
     * @param App\Repositories\PolicyPage\PolicyPageRepository $policyPageRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        PolicyPageRepository $policyPageRepository,
        ResponseHelper $responseHelper,
        Request $request
    ) {
        $this->policyPageRepository = $policyPageRepository;
        $this->responseHelper = $responseHelper;
        $this->userApiKey = $request->header('php-auth-user');
    }
    
    /**
     * Display listing of policy pages
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $policyPages = $this->policyPageRepository->getPolicyPageList($request);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($policyPages->isEmpty()) ? trans('messages.custom_error_message.ERROR_POLICY_PAGE_NOT_FOUND')
            : trans('messages.success.MESSAGE_POLICY_PAGE_LISTING');
            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $policyPages);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * Store a newly created policy page in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validataions
        $validator = Validator::make(
            $request->all(),
            [
                "page_details" => "required",
                "page_details.slug" =>
                "required|max:255|alpha_dash|unique:policy_page,slug,NULL,page_id,deleted_at,NULL",
                "page_details.translations" => "required",
                "page_details.translations.*.lang" => "required|max:2",
                "page_details.translations.*.title" => "required",
                "page_details.translations.*.sections" => "required",
                "page_details.translations.*.sections.*.title" =>
                "required_with:page_details.translations.*.sections",
                "page_details.translations.*.sections.*.description" =>
                "required_with:page_details.translations.*.sections",
            ]
        );


        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_POLICY_PAGE_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        // Create a new record
        $policyPage = $this->policyPageRepository->store($request);
        
        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_POLICY_PAGE_CREATED');
        $apiData = ['page_id' => $policyPage['page_id']];

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.POLICY_PAGE'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            $policyPage['page_id']
        ));
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Get data for parent table
            $mission = $this->policyPageRepository->find($id);
            
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_POLICY_PAGE_FOUND');
            return $this->responseHelper->success($apiStatus, $apiMessage, $mission->toArray());
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_POLICY_PAGE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_POLICY_PAGE_NOT_FOUND')
            );
        }
    }

    /**
     * Update policy page
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Server side validataions
            $validator = Validator::make(
                $request->all(),
                [
                "page_details" => "required",
                "page_details.translations.*.lang" => "required_with:page_details.translations|max:2",
                "page_details.translations.*.title" => "required_with:page_details.translations",
                "page_details.translations.*.sections" => "required_with:page_details.translations",
                "page_details.translations.*.sections.*.title" => "required_with:page_details.translations.*.sections",
                "page_details.translations.*.sections.*.description" =>
                "required_with:page_details.translations.*.sections",
                ]
            );
                  
            // If post parameter have any missing parameter
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_POLICY_PAGE_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }
            
            // For slug unique validataion
            $slugValidator = Validator::make(
                $request->page_details,
                [
                "slug" => [
                    "sometimes",
                    "required",
                    "max:255",
                    Rule::unique('policy_page')->ignore($id, 'page_id,deleted_at,NULL')],
                ]
            );
                  
            // If post parameter have any missing parameter
            if ($slugValidator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_POLICY_PAGE_REQUIRED_FIELDS_EMPTY'),
                    $slugValidator->errors()->first()
                );
            }
            
            $policyPage = $this->policyPageRepository->update($request, $id);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_POLICY_PAGE_UPDATED');
            $apiData = ['page_id' => $id];

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.POLICY_PAGE'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $id
            ));
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_POLICY_PAGE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_POLICY_PAGE_NOT_FOUND')
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $policyPage = $this->policyPageRepository->delete($id);
            
            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_POLICY_PAGE_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.POLICY_PAGE'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                null,
                null,
                $id
            ));
            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_POLICY_PAGE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_POLICY_PAGE_NOT_FOUND')
            );
        }
    }
}
