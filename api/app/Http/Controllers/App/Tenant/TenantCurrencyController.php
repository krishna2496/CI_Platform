<?php

namespace App\Http\Controllers\App\Tenant;

use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Currency\CurrencyRepository;
use App\Models\Currency;

class TenantCurrencyController extends Controller
{
    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Repositories\Currency\CurrencyRepository
     */
    private $currencyRepository;

    /**
     * Create a new controller instance.
     *
     * @param App\Helpers\Helpers $helpers
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Repositories\Currency\CurrencyRepository $currencyRepository
     * @return void
     */
    public function __construct(
        Helpers $helpers,
        ResponseHelper $responseHelper,
        CurrencyRepository $currencyRepository
    ) {
        $this->helpers = $helpers;
        $this->responseHelper = $responseHelper;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Fetch all tenant currency
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Fetch tenant all currency details
        $getTenantCurrency = $this->helpers->getTenantActivatedCurrencies($request);
        $currencyList = $this->currencyRepository->findAll();
        $allCurrencies = array();

        foreach ($currencyList as $value) {
            $code = $value->code();
            $symbol = $value->symbol();
            $allCurrencies[$code] = $symbol;
        }

        $tenantCurrencies = $getTenantCurrency->toArray();
        foreach ($tenantCurrencies as $currency) {
            if (array_key_exists($currency->code, $allCurrencies)) {
                $currency->symbol = $allCurrencies[$currency->code];
                $currency->default = $currency->default ? true : false;
            }
        }

        // Set response data
        $apiData = $getTenantCurrency->toArray();
        $apiStatus = Response::HTTP_OK;
        $apiMessage = $getTenantCurrency->isEmpty()
        ? trans('messages.success.MESSAGE_NO_RECORD_FOUND') :
        trans('messages.success.MESSAGE_TENANT_CURRENCY_LISTING');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
