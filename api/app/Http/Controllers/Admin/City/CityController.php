<?php
namespace App\Http\Controllers\Admin\City;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\City\CityRepository;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\LanguageHelper;
use Illuminate\Http\Request;
use Validator;
use App\Events\User\UserActivityLogEvent;
use Illuminate\Validation\Rule;

//!  City controller
/*!
This controller is responsible for handling city listing, store, update and delete operations.
 */
class CityController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\City\CityRepository
     */
    private $cityRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\City\CityRepository $cityRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        CityRepository $cityRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Request $request
    ) {
        $this->cityRepository = $cityRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->userApiKey = $request->header('php-auth-user');
    }

    /**
    * Fetch city by country id
    *
    * @param Illuminate\Http\Request $request
    * @param int $countryId
    * @return Illuminate\Http\JsonResponse
    */
    public function fetchCity(Request $request, int $countryId): JsonResponse
    {
        try {
            $cityList = $this->cityRepository->getCityList($request, $countryId);
           
            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($cityList->count() > 0) ? trans('messages.success.MESSAGE_CITY_LISTING')
            : trans('messages.success.MESSAGE_NO_CITY_FOUND');
            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $cityList);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_COUNTRY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_COUNTRY_NOT_FOUND')
            );
        }
    }

    /**
     * Fetch city by city id
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $cityDetails = $this->cityRepository->getCityData($id);
            
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_CITY_FOUND');
            
            return $this->responseHelper->success($apiStatus, $apiMessage, $cityDetails);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_CITY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_CITY_NOT_FOUND')
            );
        }
    }
    
    /**
     * Store a newly created cities.
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
                "country_id" => 'required|exists:country,country_id,deleted_at,NULL',
                "state_id" =>  'sometimes|required|exists:state,state_id,deleted_at,NULL',
                "cities" => 'required',
                "cities.*.translations" => 'required|array',
                "cities.*.translations.*.lang" => 'required|min:2|max:2',
                "cities.*.translations.*.name" => 'required'
            ]
        );
        
        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_CITY_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        if (!empty($request->cities)) {
            foreach ($request->cities[0]['translations'] as $key => $value) {
                $languageCode = $value['lang'];
                // Check for valid language code inside ci admin
                if (!$this->languageHelper->isValidAdminLanguageCode($languageCode)) {
                    return $this->responseHelper->error(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                        config('constants.error_codes.ERROR_TENANT_LANGUAGE_INVALID_CODE'),
                        trans('messages.custom_error_message.ERROR_TENANT_LANGUAGE_INVALID_CODE')
                    );
                }
            }
        }

        // Add cities one by one
        $createdCity = [];
        foreach ($request->cities as $key => $city) {
            // Add country id into city table
            $cityDetails = $this->cityRepository->store($request);

            // Add all translations add into city_translation table
            $createdCity[$key]['city_id'] = $city['city_id'] = $cityDetails->city_id;
            $this->cityRepository->storeCityLanguage($city);
        }

        // Set response data
        $apiData = ['city_ids' => $createdCity];
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_CITY_CREATED');
                
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.CITY'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            null
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Fetch all city
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cityList = $this->cityRepository->cityLists($request);
        $apiStatus = Response::HTTP_OK;
        $apiMessage = (!$cityList->isEmpty()) ? trans('messages.success.MESSAGE_CITY_LISTING')
        : trans('messages.success.MESSAGE_NO_CITY_FOUND');
        return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $cityList);
    }

    
    /**
     * Update resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $this->cityRepository->find($id);
            // Server side validations
            $validator = Validator::make(
                $request->all(),
                [
                    "country_id" => 'sometimes|required|exists:country,country_id,deleted_at,NULL',
                    "state_id" =>  'sometimes|required|exists:state,state_id,deleted_at,NULL',
                    "translations" => 'sometimes|required|array',
                    "translations.*.lang" => 'required|min:2|max:2',
                    "translations.*.name" => 'required'
                ]
            );
            
            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_CITY_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }
            
            // Get all countries
            $languages = $this->languageHelper->getLanguages($request);

            $this->cityRepository->update($request, $id);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_CITY_UPDATED');
                    
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.CITY'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                null
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_CITY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_CITY_NOT_FOUND')
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
        if ($this->cityRepository->hasMission($id) || $this->cityRepository->hasUser($id)) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_CITY_ENABLE_TO_DELETE'),
                trans('messages.custom_error_message.ERROR_CITY_ENABLE_TO_DELETE')
            );
        }
        try {
            $this->cityRepository->delete($id);
            
            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_CITY_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.CITY'),
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
                config('constants.error_codes.ERROR_CITY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_CITY_NOT_FOUND')
            );
        }
    }
}
