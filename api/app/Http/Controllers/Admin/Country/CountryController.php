<?php
namespace App\Http\Controllers\Admin\Country;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Repositories\Country\CountryRepository;
use App\Traits\RestExceptionHandlerTrait;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use App\Events\User\UserActivityLogEvent;
use App\Helpers\LanguageHelper;
use Illuminate\Validation\Rule;

//!  Country controller
/*!
This controller is responsible for handling country listing, store, update and delete operations.
 */
class CountryController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\Country\CountryRepository
     */
    private $countryRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\Country\CountryRepository $countryRepository
     * @param Illuminate\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        CountryRepository $countryRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Request $request
    ) {
        $this->countryRepository = $countryRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->userApiKey =$request->header('php-auth-user');
    }

    /**
    * Get country list
    *
    * @param Illuminate\Http\Request $request
    * @return Illuminate\Http\JsonResponse
    */
    public function index(Request $request) : JsonResponse
    {
        $countryList = $this->countryRepository->getCountryList($request);
        $apiStatus = Response::HTTP_OK;
        $apiMessage = (!$countryList->isEmpty()) ?
        trans('messages.success.MESSAGE_COUNTRY_LISTING') :
        trans('messages.success.MESSAGE_NO_COUNTRY_FOUND');
        return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $countryList);
    }

    /**
     * Display the specified country detail.
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $countryDetails = $this->countryRepository->getCountryData($id);
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_COUNTRY_FOUND');
            return $this->responseHelper->success($apiStatus, $apiMessage, $countryDetails);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_COUNTRY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_COUNTRY_NOT_FOUND')
            );
        }
    }

    /**
     * Store a newly created resource.
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
                "countries" => 'required',
                "countries.*.iso" => 'required|max:3|unique:country,ISO,NULL,country_id,deleted_at,NULL',
                "countries.*.translations" => 'required|array',
                "countries.*.translations.*.lang" => 'required|min:2|max:2',
                "countries.*.translations.*.name" => 'required'
            ]
        );
        
        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_COUNTRY_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        if (!empty($request->countries)) {
            foreach ($request->countries[0]['translations'] as $key => $value) {
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

        // Add countries one by one
        $createdCountries = [];
        
        foreach ($request->countries as $key => $country) {
            // Add country ISO into country table
            $countryDetails = $this->countryRepository->store($country);
            // Add all translations add into country_translation table
            $createdCountries[$key]['country_id'] = $country['country_id'] = $countryDetails->country_id;
        }

        // Set response data
        $apiData = ['country_ids' => $createdCountries];
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_COUNTRY_CREATED');
                
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.COUNTRY'),
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
     * Update resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $this->countryRepository->find($id);
            // Server side validations
            $validator = Validator::make(
                $request->all(),
                [
                    "iso" => [
                        "sometimes",
                        "required",
                        "max:3",
                        Rule::unique('country')->ignore($id, 'country_id,deleted_at,NULL')],
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
                    config('constants.error_codes.ERROR_COUNTRY_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }
            // Get all countries
            $languages = $this->languageHelper->getLanguages($request);

            $this->countryRepository->update($request, $id);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_COUNTRY_UPDATED');
                    
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.COUNTRY'),
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
                config('constants.error_codes.ERROR_COUNTRY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_COUNTRY_NOT_FOUND')
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
        if ($this->countryRepository->hasMission($id) || $this->countryRepository->hasUser($id)) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_COUNTRY_ENABLE_TO_DELETE'),
                trans('messages.custom_error_message.ERROR_COUNTRY_ENABLE_TO_DELETE')
            );
        }
        try {
            $this->countryRepository->delete($id);
            
            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_COUNTRY_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.COUNTRY'),
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
                config('constants.error_codes.ERROR_COUNTRY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_COUNTRY_NOT_FOUND')
            );
        }
    }
}
