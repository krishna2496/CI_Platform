<?php
namespace App\Http\Controllers\Admin\Slider;

use App\Events\User\UserActivityLogEvent;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Helpers\S3Helper;
use App\Http\Controllers\Controller;
use App\Repositories\Slider\SliderRepository;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Validator;

//!  Slider controller
/*!
This controller is responsible for handling slider listing, store, update and delete operations.
 */
class SliderController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\Slider\SliderRepository
     */
    private $sliderRepository;

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
     * @var string
     */
    private $userApiKey;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\Slider\SliderRepository $sliderRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param  App\Helpers\Helpers $helpers
     * @param  App\Helpers\S3Helper $s3helper
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        SliderRepository $sliderRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers,
        S3Helper $s3helper,
        Request $request
    ) {
        $this->sliderRepository = $sliderRepository;
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
        $this->s3helper = $s3helper;
        $this->userApiKey =$request->header('php-auth-user');
    }

    /**
     * Store slider details.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validataions
        $validator = Validator::make(
            $request->toArray(),
            [
                "url" => "required|url|valid_media_path",
                "translations.*.lang" => "max:2",
                "sort_order" => "numeric|min:0"
            ]
        );

        // If post parameter have any missing parameter
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_SLIDER_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        // Get total count of "slider"
        $sliderCount = $this->sliderRepository->getAllSliderCount();

        // Prevent data insertion if user is trying to insert more than defined slider limit records
        if ($sliderCount >= config('constants.SLIDER_LIMIT')) {
            // Set response data
            return $this->responseHelper->error(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_SLIDER_LIMIT'),
                trans('messages.custom_error_message.ERROR_SLIDER_LIMIT')
            );
        } else {
            // Create new slider
            $slider = $this->sliderRepository->storeSlider($request->toArray());

            // Upload slider image on S3 server
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            $imageUrl = "";

            $sliderId = $slider->slider_id;
            $imageUrl = $this->s3helper->uploadFileOnS3Bucket(
                $request->url,
                $tenantName,
                "slider/$sliderId"
            );

            $this->sliderRepository->updateSlider([
                'url' => $imageUrl
            ], $sliderId);

            $request->merge(['url' => $imageUrl]);

            // Set response data
            $apiData = ['slider_id' => $slider->slider_id];
            $apiStatus = Response::HTTP_CREATED;
            $apiMessage = trans('messages.success.MESSAGE_SLIDER_ADD_SUCCESS');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.SLIDER'),
                config('constants.activity_log_actions.CREATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $slider->slider_id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        }
    }

    /**
     * Update slider details.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Server side validataions
        $validator = Validator::make(
            $request->toArray(),
            [
                "url" => "sometimes|required|url",
                "translations.*.lang" => "max:2",
                "sort_order" => "numeric|min:0"
            ]
        );

        // If post parameter have any missing parameter
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_SLIDER_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        try {
            $this->sliderRepository->find($id);
            // Upload slider image on S3 server
            $tenantName = $this->helpers->getSubDomainFromRequest($request);

            if (isset($request->url)) {
                $imageUrl = "";
                $imageUrl = $this->s3helper->uploadFileOnS3Bucket(
                    $request->url,
                    $tenantName,
                    "slider/$id"
                );
                $request->merge(['url' => $imageUrl]);
            }

            // Update slider
            $this->sliderRepository->updateSlider($request->toArray(), $id);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_SLIDER_UPDATED_SUCCESS');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.SLIDER'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_SLIDER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_SLIDER_NOT_FOUND')
            );
        } catch (Exception $e) {
            // Response error unable to upload file on S3
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_SLIDER_IMAGE_UPLOAD'),
                trans('messages.custom_error_message.ERROR_SLIDER_IMAGE_UPLOAD')
            );
        }
    }

    /**
     * Get tenant slider
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $slider = $this->sliderRepository->getSliders();
        $apiStatus = Response::HTTP_OK;
        $apiMessage = ($slider->isEmpty()) ? trans('messages.success.MESSAGE_NO_SLIDER_FOUND') :
            trans('messages.success.MESSAGE_SLIDERS_LIST');

        return $this->responseHelper->success($apiStatus, $apiMessage, $slider->toArray());
    }

    /**
     * Delete slider.
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->sliderRepository->delete($id);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_SLIDER_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.SLIDER'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                [],
                null,
                $id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_SLIDER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_SLIDER_NOT_FOUND')
            );
        }
    }
}
