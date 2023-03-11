<?php
namespace App\Http\Controllers\App\Country;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Repositories\Country\CountryRepository;
use App\Traits\RestExceptionHandlerTrait;
use InvalidArgumentException;
use Illuminate\Http\Request;
use App\Helpers\LanguageHelper;
use App\Transformations\CountryTransformable;

//!  Country controller
/*!
This controller is responsible for handling country listing operation.
 */
class CountryController extends Controller
{
    use RestExceptionHandlerTrait, CountryTransformable;
    /**
     * @var App\Repositories\Country\CountryRepository
     */
    private $countryRepository;

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
     * @param App\Repositories\Country\CountryRepository $countryRepository
     * @param Illuminate\Helpers\ResponseHelper $responseHelper
     * @param Illuminate\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    public function __construct(
        CountryRepository $countryRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper
    ) {
        $this->countryRepository = $countryRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
    }

    /**
    * Get country list
    *
    * @param Illuminate\Http\Request $request
    * @return Illuminate\Http\JsonResponse
    */
    public function index(Request $request) : JsonResponse
    {
        // Get language id
        $languageId = $this->languageHelper->getLanguageId($request);

        // Fetch country lists
        $countryList = $this->countryRepository->countryList();

        // Get tenant default language
        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);

        // Get countries with more information
        $detailed = filter_var(
            $request->get('detailed'),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if (!$countryList->isEmpty()) {
            // Transform country details
            $countryDetails = $this->countryTransform(
                $countryList->toArray(),
                $languageId,
                $defaultTenantLanguage->language_id,
                $detailed
            );
        }

        $apiData = isset($countryDetails) ? $countryDetails : $countryList->toArray();
        $apiStatus = Response::HTTP_OK;
        $apiMessage = (!empty($apiData)) ?
        trans('messages.success.MESSAGE_COUNTRY_LISTING') :
        trans('messages.success.MESSAGE_NO_COUNTRY_FOUND');
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
