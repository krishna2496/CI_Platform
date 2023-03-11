<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Traits\RestExceptionHandlerTrait;
use App\Helpers\ResponseHelper;
use App\Repositories\ActivityLog\ActivityLogRepository;
use Validator;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;

//!  Activity log controller
/*!
This controller is responsible for handling activity log listing operation.
 */
class ActivityLogController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\ActivityLog\ActivityLogRepository
     */
    private $activityLogRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;
    
    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\ActivityLog\ActivityLogRepository $activityLogRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(
        ActivityLogRepository $activityLogRepository,
        ResponseHelper $responseHelper
    ) {
        $this->activityLogRepository = $activityLogRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->toArray(),
            [
                'type' => 'sometimes|in:'.implode(',', config("constants.activity_log_types")),
                'action' => 'sometimes|in:'.implode(',', config("constants.activity_log_actions")),
                'order' => 'sometimes|in:desc,asc',
                'from_date' => 'required_with:to_date|date_format:Y-m-d',
                'to_date' => 'required_with:from_date|date_format:Y-m-d',
            ]
        );
        
        // If validator fails
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_ACTIVITY_LOG_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        $activityLogs = $this->activityLogRepository->getActivityLogs($request);

        $apiData = $activityLogs;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = ($apiData->count()) ?
        trans('messages.success.MESSAGE_ACTIVITY_LOGS_ENTRIES_LISTING') :
        trans('messages.success.MESSAGE_NO_ACTIVITY_LOGS_ENTRIES_FOUND');

        return $this->responseHelper->successWithPagination(
            $apiData,
            $apiStatus,
            $apiMessage
        );
    }
}
