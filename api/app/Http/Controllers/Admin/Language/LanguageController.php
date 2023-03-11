<?php
namespace App\Http\Controllers\Admin\Language;

use App\Http\Controllers\Controller;
use App\Services\FrontendTranslationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exceptions\BucketNotFoundException;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use App\Helpers\Helpers;
use App\Helpers\S3Helper;
use Validator;
use App\Exceptions\TenantDomainNotFoundException;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\FileNotFoundException;
use App\Events\User\UserActivityLogEvent;
use App\Helpers\LanguageHelper;

//!  Language controller
/*!
This controller is responsible for handling language file listing operation.
 */
class LanguageController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\S3Helper
     */
    private $s3helper;

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
     * @var FrontendTranslationService
     */
    private $frontendTranslationService;

    /**
     * Create a new controller instance.
     *
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\Helpers $helpers
     * @param  App\Helpers\S3Helper $s3helper
     * @param Illuminate\Http\Request $request
     * @param App\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    public function __construct(
        ResponseHelper $responseHelper,
        Helpers $helpers,
        S3Helper $s3helper,
        Request $request,
        LanguageHelper $languageHelper,
        FrontendTranslationService $frontendTranslationService
    ) {
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
        $this->s3helper = $s3helper;
        $this->userApiKey = $request->header('php-auth-user');
        $this->languageHelper = $languageHelper;
        $this->frontendTranslationService = $frontendTranslationService;
    }

    /**
     * Fetch language file url.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $isoCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchGenericTranslations(Request $request, $isoCode): JsonResponse
    {
        // Server side validations
        $validator = Validator::make(
            [
                'isoCode' => $isoCode,
            ],
            [
                'isoCode' => 'required|max:2|min:2',
            ]
        );

        // If post parameter have any missing parameter
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_LANGUAGE_INVALID_CODE'),
                $validator->errors()->first()
            );
        }

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

        // Fetch default translations and return them
        $apiData = $this->frontendTranslationService
            ->getGenericTranslationsForLanguage($tenantName, $isoCode)
            ->toArray();

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_LANGUAGE_FILE_FOUND');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Fetch language file url.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $isoCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCustomTranslations(Request $request, $isoCode): JsonResponse
    {
        // Server side validations
        $validator = Validator::make(
            [
                'isoCode' => $isoCode,
            ],
            [
                'isoCode' => 'required|max:2|min:2',
            ]
        );

        // If post parameter have any missing parameter
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_LANGUAGE_INVALID_CODE'),
                $validator->errors()->first()
            );
        }

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

        // Fetch default translations and return them
        $apiData = $this->frontendTranslationService
            ->getCustomTranslationsForLanguage($tenantName, $isoCode)
            ->toArray();

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_LANGUAGE_FILE_FOUND');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * It will update language file on S3
     *
     * @param \Illuminate\Http\Request $request
     * @param string $isoCode
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateTranslations(Request $request, $isoCode): JsonResponse
    {
        // Server side validations
        $translations = $request->getContent();
        $validator = Validator::make(
            [
                'isoCode' => $isoCode,
                'translations' => $translations,
            ],
            [
                "isoCode" => "required|max:2|min:2",
                "translations" => "required"
            ]
        );

        // If post parameter have any missing parameter
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_LANGUAGE_FILE_UPLOAD_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        // Validate json data
        if (json_decode($translations) === null) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_LANGUAGE_INVALID_JSON_FORMAT'),
                trans('messages.custom_error_message.ERROR_TENANT_LANGUAGE_INVALID_JSON_FORMAT')
            );
        }

        // Check for valid language code
        if (!$this->languageHelper->getTenantLanguageByCode($request, $isoCode)) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_LANGUAGE_INVALID'),
                trans('messages.custom_error_message.ERROR_TENANT_LANGUAGE_INVALID')
            );
        }

        // Get domain name from request and use as tenant name.
        $tenantName = $this->helpers->getSubDomainFromRequest($request);

        // Store the custom translations on S3
        $this->frontendTranslationService->storeCustomTranslations($tenantName, $isoCode, $translations);

        // Break the cache then warm it up
        $this->frontendTranslationService->clearCache($tenantName, $isoCode);
        $this->frontendTranslationService->getCustomTranslationsForLanguage($tenantName, $isoCode);

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.TENANT_LANGUAGE'),
            config('constants.activity_log_actions.UPDATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            [
                'isoCode' => $isoCode,
            ],
            null,
            null
        ));
        $apiData = ["isoCode" => $isoCode];
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_LANGUAGE_UPDATED_SUCESSFULLY');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
