<?php
namespace App\Http\Controllers\App\FooterPage;

use App\Repositories\FooterPage\FooterPageRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\FooterPage;
use App\Models\FooterPagesLanguage;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\RestExceptionHandlerTrait;
use InvalidArgumentException;

//!  FooterPage controller
/*!
This controller is responsible for handling footerpage listing and show operations.
 */
class FooterPageController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\FooterPage\FooterPageRepository
     */
    private $footerPageRepository;
    
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;
    
    /**
     * Create a new controller instance.
     *
     * @param  App\Repositories\FooterPage\FooterPageRepository $footerPageRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(FooterPageRepository $footerPageRepository, ResponseHelper $responseHelper)
    {
        $this->footerPageRepository = $footerPageRepository;
        $this->responseHelper = $responseHelper;
    }
    
    /**
     * Display a listing of CMS pages.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get data for parent table
            $pageList = $this->footerPageRepository->getPageList($request);
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_FOOTER_PAGE_LISTING');
            $apiMessage = ($pageList->isEmpty()) ? trans('messages.success.MESSAGE_NO_RECORD_FOUND') :
             trans('messages.success.MESSAGE_FOOTER_PAGE_LISTING');
            return $this->responseHelper->success($apiStatus, $apiMessage, $pageList->toArray());
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Illuminate\Http\Request $request
     * @param string $slug
     * @return Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        try {
            // Get data for parent table
            $footerPage = $this->footerPageRepository->getPageDetail($request, $slug);
          
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_FOOTER_PAGE_FOUND');
            return $this->responseHelper->success($apiStatus, $apiMessage, $footerPage->toArray());
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NO_DATA_FOUND_FOR_SLUG'),
                trans('messages.custom_error_message.ERROR_NO_DATA_FOUND_FOR_SLUG')
            );
        }
    }

    /**
     * Display a listing of CMS pages.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function cmsList(): JsonResponse
    {
        // Get data for parent table
        $pageDetailList = $this->footerPageRepository->getPageDetailList();

        $apiStatus = Response::HTTP_OK;
        $apiMessage = ($pageDetailList->isEmpty()) ? trans('messages.success.MESSAGE_NO_DATA_FOUND') :
        trans('messages.success.MESSAGE_FOOTER_PAGE_LISTING');

        return $this->responseHelper->success($apiStatus, $apiMessage, $pageDetailList->toArray());
    }
}
