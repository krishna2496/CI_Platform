<?php
namespace App\Http\Controllers\App\Tenant;

use App\Helpers\S3Helper;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\TenantOption;
use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\Slider\SliderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;
use Validator;

//!  Tenant option controller
/*!
This controller is responsible for handling tenant option listing, custom css listing
and get tenant option value operations.
 */
class TenantOptionController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var TenantOptionRepository
     */
    private $tenantOption;

    /**
     * @var SliderRepository
     */
    private $sliderRepository;

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
     * Create a new controller instance.
     *
     * @param App\Repositories\TenantOption\TenantOptionRepository $tenantOptionRepository
     * @param App\Repositories\Slider\SliderRepository $sliderRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(
        TenantOptionRepository $tenantOptionRepository,
        SliderRepository $sliderRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Helpers $helpers
    ) {
        $this->tenantOptionRepository = $tenantOptionRepository;
        $this->sliderRepository = $sliderRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->helpers = $helpers;
    }

    /**
     * Get tenant options from table `tenant_options`
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function getTenantOption(Request $request): JsonResponse
    {
        $tenantDetail = $this->helpers->getTenantDetail($request);
        $optionData = [
            'tenantName' => $tenantDetail->name
        ];

        // Find custom data
        $data = $this->tenantOptionRepository->getOptions();

        if ($data) {
            foreach ($data as $key => $value) {
                $optionValue = $value->option_value;
                if ($value->option_name === TenantOption::SAML_SETTINGS
                    && $optionValue
                ) {
                    $optionValue = [
                      'saml_access_only' => $optionValue['saml_access_only'],
                      'sso_url' => route('saml.sso', ['t' => $optionValue['idp_id'], 'tenant' => $tenantDetail->tenant_id]),
                      'slo_url' => $optionValue['idp']['singleLogoutService']['url'],
                    ];
                }
                $optionData[$value->option_name] = $optionValue;
            }
        }

        // For slider
        $sliderData = array();
        $sliders = $this->sliderRepository->getAllSliders();
        foreach ($sliders as $key => $value) {
            $value['slider_detail']['translations'] = $value['translations'];
            unset($value['translations']);
            $sliderData[] = $value;
        }
        $optionData['sliders'] = $sliderData;

        $tenantLanguages = $this->languageHelper->getTenantLanguages($request);

        if ($tenantLanguages->count() > 0) {
            foreach ($tenantLanguages as $key => $value) {
                if ($value->default === "1") {
                    $optionData['defaultLanguage'] = strtoupper($value->code);
                    $optionData['defaultLanguageId'] = $value->language_id;
                }
                $optionData['language'][$value->language_id] = strtoupper($value->code);
            }
        }

        return $this->responseHelper
            ->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_TENANT_OPTIONS_LIST'),
                $optionData
            );
    }

    /**
     * Get tenant custom css from table `tenant_options`
     *
     * @return JsonResponse
     */
    public function getCustomCss(Request $request): JsonResponse
    {
        $isCustomCssEnabled = false;
        $tenantCustomCssUrl = '';

        // Check presence of custom css option
        try {
            $tenantOption = $this->tenantOptionRepository->getOptionWithCondition(['option_name' => 'custom_css']);
            $isCustomCssEnabled = $tenantOption !== null && $tenantOption->option_value === 1;
        } catch (\Exception $e) {
            /*
             * If there was some trouble when retrieving this option
             * we have nothing to do as the default is to consider
             * the custom css option turned off
             */
        }

        if ($isCustomCssEnabled) {
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            $assetsFolder = env('AWS_S3_ASSETS_FOLDER_NAME');
            $customCssName = env('S3_CUSTOME_CSS_NAME');

            $tenantCustomCssUrl = S3Helper::makeTenantS3BaseUrl($tenantName)
                . $assetsFolder
                . '/css/'
                . $customCssName;
        }

        $apiData = [
            'custom_css' => $isCustomCssEnabled ? $tenantCustomCssUrl : false,
        ];
        $apiStatus = Response::HTTP_OK;

        return $this->responseHelper->success($apiStatus, '', $apiData);
    }

    /**
     * Get tenant custom favicon from table `tenant_options`
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getCustomFavicon(Request $request): JsonResponse
    {
        $isCustomFaviconEnabled = false;
        $tenantCustomFaviconUrl = '';

        // Check presence of custom favicon option
        try {
            $tenantOption = $this->tenantOptionRepository->getOptionWithCondition(['option_name' => 'custom_favicon']);
            $isCustomFaviconEnabled = $tenantOption !== null && $tenantOption->option_value === 1;
        } catch (\Exception $e) {
            /*
             * If there was some trouble when retrieving this option
             * we have nothing to do as the default is to consider
             * the custom favicon option turned off
             */
        }

        if ($isCustomFaviconEnabled) {
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            $assetsFolder = env('AWS_S3_ASSETS_FOLDER_NAME');
            $customFaviconName = config('constants.AWS_S3_CUSTOM_FAVICON_NAME');

            $customFaviconS3Path = $tenantName . '/'
                . $assetsFolder
                . '/images/favicon/'
                . $customFaviconName;

            if (Storage::disk('s3')->exists($customFaviconS3Path)) {
                $tenantCustomFaviconUrl = Storage::disk('s3')->url($customFaviconS3Path);
            }
        }

        $apiData = [
            'custom_favicon' => $tenantCustomFaviconUrl,
        ];
        $apiStatus = Response::HTTP_OK;
        $apiMessage = $tenantCustomFaviconUrl !== '' ? trans('messages.success.MESSAGE_FAVICON_UPLOADED') :
            trans('messages.custom_error_message.ERROR_NO_DATA_FOUND');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Display tenant option value
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function fetchTenantOptionValue(Request $request): JsonResponse
    {
        // Server side validataions
        $validator = Validator::make(
            $request->all(),
            [
                "option_name" => "required"
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_OPTION_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        // Fetch tenant option value
        $tenantOptionDetail = $this->tenantOptionRepository->getOptionValue($request->option_name);
        $apiMessage = ($tenantOptionDetail->isEmpty())
        ? trans('messages.custom_error_message.ERROR_TENANT_OPTION_NOT_FOUND')
        : trans('messages.success.MESSAGE_TENANT_OPTION_FOUND');
        $apiStatus = Response::HTTP_OK;

        return $this->responseHelper->success($apiStatus, $apiMessage, $tenantOptionDetail->toArray());
    }
}
