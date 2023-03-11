<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class TenantActivatedCurrenciesController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @param ResponseHelper $responseHelper
     *
     * @return void
     */
    public function __construct(
        Helpers $helpers,
        ResponseHelper $responseHelper
    ) {
        $this->helpers = $helpers;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Display a listing of tenant's activated currencies
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currencies = $this->helpers->getTenantActivatedCurrencies($request)->toArray();
            if (!empty($currencies)) {
                foreach ($currencies as &$currency) {
                    $currency->default = $currency->default ? true : false;
                }
            }

            return $this->responseHelper->success(
                Response::HTTP_OK,
                $currencies
                    ? trans('messages.success.MESSAGE_TENANT_ACTIVATED_CURRENCIES_FOUND')
                    : trans('messages.success.MESSAGE_TENANT_ACTIVATED_CURRENCIES_EMPTY'),
                $currencies
            );
        } catch (Exception $e) {
            return $this->responseHelper->error(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                config('constants.error_codes.ERROR_RETRIEVING_TENANT_ACTIVATED_CURRENCIES'),
                trans('messages.custom_error_message.ERROR_RETRIEVING_TENANT_ACTIVATED_CURRENCIES')
            );
        }
    }
}