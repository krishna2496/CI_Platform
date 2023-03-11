<?php
namespace App\Http\Controllers\App\PolicyPage;

use App\Repositories\PolicyPage\PolicyPageRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\PolicyPage;
use App\Models\PolicyPagesLanguage;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Helpers\LanguageHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\RestExceptionHandlerTrait;

//!  Policypage controller
/*!
This controller is responsible for handling policypage listing and show operations.
 */
class PolicyPageController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\PolicyPage\PolicyPageRepository
     */
    private $policyPageRepository;
    
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;
    
    /**
     * Create a new controller instance.
     *
     * @param  App\Repositories\PolicyPage\PolicyPageRepository $policyPageRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    public function __construct(
        PolicyPageRepository $policyPageRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper
    ) {
        $this->policyPageRepository = $policyPageRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
    }
    
    /**
     * Display a listing of policy pages.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $pageList = $this->policyPageRepository->getPageList($request);
        $apiStatus = Response::HTTP_OK;
        $apiMessage = ($pageList->isEmpty()) ? trans('messages.success.MESSAGE_NO_RECORD_FOUND') :
            trans('messages.success.MESSAGE_POLICY_PAGE_LISTING');
        return $this->responseHelper->success($apiStatus, $apiMessage, $pageList->toArray());
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
            $policyPage = $this->policyPageRepository->getPageDetail($request, $slug);
          
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_POLICY_PAGE_FOUND');
            return $this->responseHelper->success($apiStatus, $apiMessage, $policyPage->toArray());
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NO_DATA_FOUND_FOR_SLUG'),
                trans('messages.custom_error_message.ERROR_NO_DATA_FOUND_FOR_SLUG')
            );
        }
    }
}
