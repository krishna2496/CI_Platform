<?php
namespace App\Http\Controllers\App\Mission;

use App\Repositories\Mission\MissionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Traits\RestExceptionHandlerTrait;
use Validator;
use App\Events\User\UserActivityLogEvent;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;

//!  Mission rating controller
/*!
This controller is responsible for handling mission rating store operation.
 */
class MissionRatingController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var MissionRepository
     */
    private $missionRepository;
    
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;
	
	/**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;
	
    /**
     * Create a new Mission rating controller instance.
     *
     * @param App\Repositories\Mission\MissionRepository $missionRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
	 * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @return void
     */
    public function __construct(
        MissionRepository $missionRepository,
        ResponseHelper $responseHelper,
		TenantActivatedSettingRepository $tenantActivatedSettingRepository
    ) {
        $this->missionRepository = $missionRepository;
        $this->responseHelper = $responseHelper;
		$this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
    }

    /**
     * Store a newly created resource in storage.
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
                "rating" => "required|numeric|min:0.5|max:5",
                "mission_id" => "integer|required|exists:mission,mission_id,deleted_at,NULL"
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_MISSION_RATING_INVALID_DATA'),
                $validator->errors()->first()
            );
        }
        
		$getActivatedTenantSettings = $this->tenantActivatedSettingRepository
        ->getAllTenantActivatedSetting($request);
		
		$missionRatingVolunteer = config('constants.tenant_settings.MISSION_RATING_VOLUNTEER');
		
		if (in_array($missionRatingVolunteer, $getActivatedTenantSettings)) {
            $missionApplicationStatus = array(
				config('constants.application_status.AUTOMATICALLY_APPROVED')
			);

			//Check mission application status
			$applicationStatus = $this->missionRepository->checkUserMissionApplicationStatus(
				$request->mission_id,
				$request->auth->user_id,
				$missionApplicationStatus
			);
			if ($applicationStatus) {
				return $this->responseHelper->error(
					Response::HTTP_UNPROCESSABLE_ENTITY,
					Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
					config('constants.error_codes.MISSION_APPLICATION_NOT_APPROVED'),
					trans('messages.custom_error_message.MISSION_APPLICATION_NOT_APPROVED')
				);
			}
        }
		
        // Store mission rating
        $missionRating = $this->missionRepository->storeMissionRating($request->auth->user_id, $request->toArray());

        // Set response data
        $apiStatus = ($missionRating->wasRecentlyCreated) ? Response::HTTP_CREATED : Response::HTTP_OK;
        $apiMessage = ($missionRating->wasRecentlyCreated) ? trans('messages.success.MESSAGE_RATING_ADDED')
        : trans('messages.success.MESSAGE_RATING_UPDATED');
        
        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.MISSION'),
            config('constants.activity_log_actions.RATED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $request->toArray(),
            $request->auth->user_id,
            $request->mission_id
        ));
        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
