<?php
namespace App\Http\Controllers\App\Language;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\FrontendTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

//!  Language controller
/*!
This controller is responsible for handling language file listing operation.
 */
class LanguageController extends Controller
{
    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

	/**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

	/**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var FrontendTranslationService
     */
    private $frontendTranslationService;

    /**
     * Create a new controller instance.
     *
     * @param  App\Helpers\S3Helper $s3helper
     * @param  App\Helpers\Helpers $helpers
	 * @param App\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    public function __construct(
        Helpers $helpers,
        LanguageHelper $languageHelper,
        ResponseHelper $responseHelper,
        FrontendTranslationService $frontendTranslationService
    ) {
        $this->helpers = $helpers;
		$this->languageHelper = $languageHelper;
		$this->responseHelper = $responseHelper;
		$this->frontendTranslationService = $frontendTranslationService;
    }

    /**
     * Fetch language file
     *
     * @param \Illuminate\Http\Request $request
     * @param String $isoCode
     * @return JsonResponse
    */
    public function fetchLanguageFile(Request $request, String $isoCode)
    {
		// Check for valid language code
		if (!$this->languageHelper->getTenantLanguageByCode($request, $isoCode)) {
			return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_LANGUAGE_INVALID_CODE'),
                trans('messages.custom_error_message.ERROR_TENANT_LANGUAGE_INVALID_CODE')
            );
		}

        // Get domain name from request and use as tenant name.
        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        $translations = $this->frontendTranslationService->getCustomTranslationsForLanguage($tenantName, $isoCode);

        return new JsonResponse([
            'locale' => $isoCode,
            'data' => $translations,
        ]);
    }
}
