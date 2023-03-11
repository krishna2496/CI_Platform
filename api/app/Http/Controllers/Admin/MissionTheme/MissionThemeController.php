<?php
namespace App\Http\Controllers\Admin\MissionTheme;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Repositories\MissionTheme\MissionThemeRepository;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Validator;
use Illuminate\Validation\Rule;
use App\Events\User\UserActivityLogEvent;

//!  Mission theme controller
/*!
This controller is responsible for handling mission theme listing, show, store, update and delete operations.
 */
class MissionThemeController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\MissionTheme\MissionThemeRepository
     */
    private $missionThemeRepository;

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
     * @param App\Repositories\User\MissionThemeRepository $missionThemeRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        MissionThemeRepository $missionThemeRepository,
        ResponseHelper $responseHelper,
        Request $request
    ) {
        $this->missionThemeRepository = $missionThemeRepository;
        $this->responseHelper = $responseHelper;
        $this->userApiKey = $request->header('php-auth-user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $missionThemes = $this->missionThemeRepository->missionThemeDetails($request);

            $apiMessage = ($missionThemes->isEmpty())
                ? trans('messages.success.MESSAGE_NO_RECORD_FOUND')
                : trans('messages.success.MESSAGE_THEME_LISTING');

            return $this->responseHelper
                ->successWithPagination(Response::HTTP_OK, $apiMessage, $missionThemes);

        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * Store a newly created resource in storage.
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
                "theme_name" => "required|max:255|
                unique:mission_theme,theme_name,NULL,mission_theme_id,deleted_at,NULL",
                "translations" => "required",
                "translations.*.lang" => "required_with:translations|max:2"
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_THEME_INVALID_DATA'),
                $validator->errors()->first()
            );
        }
        // Create new mission theme
        $missionTheme = $this->missionThemeRepository->store($request->all());

        // Set response data
        $apiData = ['mission_theme_id' => $missionTheme->mission_theme_id];
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_THEME_CREATED');

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.MISSION_THEME'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            $missionTheme->mission_theme_id
        ));
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Server side validataions
            $validator = Validator::make(
                $request->all(),
                ["theme_name" => [
                    "sometimes",
                    "required",
                    Rule::unique('mission_theme')->ignore($id, 'mission_theme_id,deleted_at,NULL')],
                "translations" => "sometimes|required",
                "translations.*.lang" => "required_with:translations|max:2"
                ]
            );

            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_THEME_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }

            // Update mission theme
            $missionTheme = $this->missionThemeRepository->update($request->toArray(), $id);

            // Set response data
            $apiData = ['mission_theme_id' => $missionTheme->mission_theme_id];
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_THEME_UPDATED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MISSION_THEME'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $missionTheme->mission_theme_id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_THEME_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_THEME_NOT_FOUND')
            );
        }
    }

    /**
     * Display the specified missionTheme detail.
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $missionThemeDetail = $this->missionThemeRepository->find($id);

            $apiData = $missionThemeDetail->toArray();
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_THEME_FOUND');

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_THEME_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_THEME_NOT_FOUND')
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
        if ($this->missionThemeRepository->hasMission($id)) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_THEME_UNABLE_TO_DELETE'),
                trans('messages.custom_error_message.ERROR_THEME_UNABLE_TO_DELETE')
            );
        }
        try {
            $missionTheme = $this->missionThemeRepository->delete($id);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_THEME_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MISSION_THEME'),
                config('constants.activity_log_actions.DELETED'),
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
                config('constants.error_codes.ERROR_THEME_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_THEME_NOT_FOUND')
            );
        }
    }
}
