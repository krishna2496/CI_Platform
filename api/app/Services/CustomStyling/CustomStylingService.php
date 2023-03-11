<?php
namespace App\Services\CustomStyling;

use App\Jobs\UpdateStyleSettingsJob;
use App\Repositories\TenantOption\TenantOptionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exceptions\TenantDomainNotFoundException;
use App\Exceptions\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Helpers;
use Validator;
use App\Helpers\ResponseHelper;

class CustomStylingService
{
    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var ResponseHelper
     */
    private $responseHelper;

    /**
     * @var TenantOptionRepository
     */
    private $tenantOptionRepository;

    /**
     * CustomStylingService constructor.
     * @param TenantOptionRepository $tenantOptionRepository
     * @param Helpers $helpers
     * @param ResponseHelper $responseHelper
     */
    public function __construct(
        TenantOptionRepository $tenantOptionRepository,
        Helpers $helpers,
        ResponseHelper $responseHelper
    ) {
        $this->tenantOptionRepository = $tenantOptionRepository;
        $this->helpers = $helpers;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Upload file on S3 and validate it
     *
     * @param Illuminate\Http\Request $request
     * @return null
     */
    public function uploadFileOnS3(Request $request)
    {
        $tenantName = $this->helpers->getSubDomainFromRequest($request);

        $file = $request->file('image_file');
        $fileName = $request->image_name;

        if (!Storage::disk('s3')->exists($tenantName.'/assets/images/'.$fileName)) {
            throw new FileNotFoundException(
                trans('messages.custom_error_message.ERROR_IMAGE_FILE_NOT_FOUND_ON_S3'),
                config('constants.error_codes.ERROR_IMAGE_FILE_NOT_FOUND_ON_S3')
            );
        }
        // Upload file on s3
        Storage::disk('s3')->put(
            '/'.$tenantName.'/assets/images/'.$fileName,
            file_get_contents(
                $file->getRealPath()
            ),
            [
                'mimetype' => $file->getMimeType()
            ]
        );
        return null;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function updateCustomStyle(Request $request): bool
    {
        $isVariableScssFile = false;
        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        $fileName = $request->custom_scss_file_name;

        // Update primary and secondary color, if any
        $this->tenantOptionRepository->updateStyleSettings($request);

        $file = $request->file('custom_scss_file');
        if ($file && $file->isValid()) {
            $filePath = $tenantName
                . '/' . env('AWS_S3_ASSETS_FOLDER_NAME')
                . '/' . config('constants.AWS_S3_SCSS_FOLDER_NAME')
                . '/' . $fileName;

            Storage::disk('s3')->put($filePath, file_get_contents($file));

            /*
             * If user uploaded '_variables.scss', it has priority
             * over tenant option 'primary_color'
             */
            $isVariableScssFile = $fileName === config('constants.AWS_CUSTOM_STYLE_VARIABLE_FILE_NAME');
        }

        // Build options for compiling
        $options['isVariableScss'] = $isVariableScssFile ? 1 : 0;
        $options['primary_color'] = $request->primary_color;

        dispatch(new UpdateStyleSettingsJob($tenantName, $options, $fileName));

        return true;
    }

    /**
     * It will check uploading file validation
     *
     * @param Illuminate\Http\Request $request
     * @return Null|JsonResponse
     */
    public function checkFileValidations(Request $request): ?JsonResponse
    {
        $validFileTypesArray = ['image/jpeg','image/svg+xml','image/png'];

        $file = $request->file('image_file');
        $fileName = $request->image_name;
        $fileNameExtension = substr(strrchr($fileName, '.'), 1);

        if ($fileNameExtension !== $file->getClientOriginalExtension()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_EXTENSION_OF_FILE'),
                trans('messages.custom_error_message.ERROR_NOT_VALID_IMAGE_FILE_EXTENSION')
            );
        }

        // If request parameter have any error
        if (!in_array($file->getMimeType(), $validFileTypesArray) &&
        $fileNameExtension === $file->getClientOriginalExtension()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_NOT_VALID_EXTENSION'),
                trans('messages.custom_error_message.ERROR_NOT_VALID_IMAGE_FILE_EXTENSION')
            );
        }
        return null;
    }
}
