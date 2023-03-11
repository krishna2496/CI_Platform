<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Events\User\UserActivityLogEvent;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Helpers\S3Helper;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TenantCustomizationController extends Controller
{
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\S3Helper
     */
    private $s3helper;

    /**
     * Create a new controller instance.
     *
     * @param ResponseHelper $responseHelper
     * @param Helpers $helpers
     * @param S3Helper $s3helper
     * @return void
     */
    public function __construct(
        ResponseHelper $responseHelper,
        Helpers $helpers,
        S3Helper $s3helper
    ) {
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
        $this->s3helper = $s3helper;
    }

    /**
     * Upload favicon
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadFavicon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'favicon' => 'required|file|mimetypes:image/x-icon,image/vnd.microsoft.icon'
        ]);

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        try {
            $favicon = $request->file('favicon')->get();
        } catch (FileNotFoundException $exception) {
            return $this->responseHelper->error(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_IMAGE_UPLOAD_INVALID_DATA'),
                $exception->getMessage()
            );
        }

        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        $imagePath = $this->s3helper->uploadFaviconOnS3Bucket($favicon, $tenantName);
        $apiData = ['favicon' => $imagePath];
        $apiMessage = trans('messages.success.MESSAGE_FAVICON_UPLOADED');
        $apiStatus = Response::HTTP_OK;

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Retrieve favicon
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFavicon(Request $request): JsonResponse
    {
        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        $imagePath = $this->s3helper->retrieveFaviconFromS3Bucket($tenantName);

        $apiData = ['favicon' => $imagePath];
        $apiStatus = $imagePath ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;
        $apiMessage = $imagePath ? trans('messages.success.MESSAGE_FAVICON_UPLOADED') :
            trans('messages.custom_error_message.ERROR_NO_DATA_FOUND');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
