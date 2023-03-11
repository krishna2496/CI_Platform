<?php

namespace Tests\Unit\Http\Controllers\App\Tenant;

use Mockery;
use TestCase;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\App\Tenant\TenantCurrencyController;
use App\Repositories\Currency\CurrencyRepository;
use App\Models\Currency;

class TenantCurrencyControllerTest extends TestCase
{
    /**
     * @testdox Get tenant currency list success
     */
    public function testGetTenantCurrencyListSuccess()
    {
        $request = new Request();
        $helpers = $this->mock(Helpers::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $collection = $this->mock(Collection::class);
        $currencyRepository = $this->mock(CurrencyRepository::class);
        $currencyData = new Currency('USD', '$');
        $currencyDataArray = [$currencyData];

        $currencies = [
            (object) [
                'code'=> 'USD',
                'default' => 1
            ]
        ];

        $collectionCurrency = collect($currencies);

        $helpers->shouldReceive('getTenantActivatedCurrencies')
            ->times()
            ->with($request)
            ->andReturn($collectionCurrency);

        $currencyRepository->shouldReceive('findAll')
            ->once()
            ->andReturn($currencyDataArray);

        $apiData = $collectionCurrency->toArray();
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_CURRENCY_LISTING');

        $methodResponse = [
            'status' => $apiStatus,
            'data' => $apiData,
            'message' => $apiMessage,
        ];

        $jsonResponse = $this->getJson($methodResponse);

        $responseHelper->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, [
                (object) [
                    'code' => 'USD',
                    'default' => 1,
                    'symbol' => '$'
                ]
            ])
            ->andReturn($jsonResponse);

        $callController = $this->getController(
            $helpers,
            $responseHelper,
            $currencyRepository
        );

        $response = $callController->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Get tenant currency list empty success
     */
    public function testGetTenantCurrencyListEmptySuccess()
    {
        $request = new Request();
        $helpers = $this->mock(Helpers::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $collection = $this->mock(Collection::class);
        $currencyRepository = $this->mock(CurrencyRepository::class);
        $currencyData = new Currency('USD', '$');
        $currencyDataArray = [$currencyData];

        $currencies = [];

        $collectionCurrency = collect($currencies);

        $helpers->shouldReceive('getTenantActivatedCurrencies')
            ->times()
            ->with($request)
            ->andReturn($collectionCurrency);

        $currencyRepository->shouldReceive('findAll')
            ->once()
            ->andReturn($currencyDataArray);

        $apiData = $collectionCurrency->toArray();
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_NO_RECORD_FOUND');

        $methodResponse = [
            'status' => $apiStatus,
            'message' => $apiMessage,
        ];

        $jsonResponse = $this->getJson($methodResponse);

        $responseHelper->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, $apiData)
            ->andReturn($jsonResponse);

        $callController = $this->getController(
            $helpers,
            $responseHelper,
            $currencyRepository
        );

        $response = $callController->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
     * Create a new controller instance.
     *
     * @param  App\Helpers\Helpers $helpers
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param  App\Repositories\Currency\CurrencyRepository $currencyRepository
     * @return void
     */
    private function getController(
        Helpers $helpers,
        ResponseHelper $responseHelper,
        CurrencyRepository $currencyRepository
    ) {
        return new TenantCurrencyController(
            $helpers,
            $responseHelper,
            $currencyRepository
        );
    }

    /**
     * Mock an object
     *
     * @param string name
     * @return Mockery
     */
    private function mock($class)
    {
        return Mockery::mock($class);
    }

    /**
     * Get json reponse
     *
     * @param class name
     * @return JsonResponse
     */
    private function getJson($class)
    {
        return new JsonResponse($class);
    }
}
