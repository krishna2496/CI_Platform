<?php

namespace Tests\Unit\Http\Controllers\Admin\Tenant;

use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\Tenant\TenantActivatedCurrenciesController;
use App\Http\Controllers\Controller;
use App\Traits\RestExceptionHandlerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Mockery;
use StdClass;
use TestCase;

class TenantActivatedCurrenciesControllerTest extends TestCase
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
     * @var App\Http\Controllers\Admin\Tenant
     */
    private $activatedCurrenciesController;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->helpers = $this->mock(Helpers::class);
        $this->responseHelper = $this->mock(ResponseHelper::class);

        $this->activatedCurrenciesController = new TenantActivatedCurrenciesController(
            $this->helpers,
            $this->responseHelper
        );
    }

    /**
     * @testdox Gets tenant's activated currencies
     */
    public function testIndexSuccess()
    {
        $currency1 = new StdClass;
        $currency1->code = 'EUR';
        $currency1->default = 1;

        $currency2 = new StdClass;
        $currency2->code = 'USD';
        $currency2->default = 0;

        $request = new Request();
        $currencies = Collection::make([
            $currency1,
            $currency2
        ]);

        $this->helpers
            ->shouldReceive('getTenantActivatedCurrencies')
            ->with($request)
            ->once()
            ->andReturn($currencies);

        $this->responseHelper
            ->shouldReceive('success')
            ->with(
                Response::HTTP_OK,
                $currencies
                    ? trans('messages.success.MESSAGE_TENANT_ACTIVATED_CURRENCIES_FOUND')
                    : trans('messages.success.MESSAGE_TENANT_ACTIVATED_CURRENCIES_EMPTY'),
                $currencies->toArray()
            );

        $response = $this->activatedCurrenciesController->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Gets tenant's activated currencies
     */
    public function testIndexException()
    {
        $request = new Request();
        $this->helpers
            ->shouldReceive('getTenantActivatedCurrencies')
            ->with($request)
            ->once()
            ->andThrow(new Exception('An unknown error occured'));

        $this->responseHelper
            ->shouldReceive('error')
            ->with(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                config('constants.error_codes.ERROR_RETRIEVING_TENANT_ACTIVATED_CURRENCIES'),
                trans('messages.custom_error_message.ERROR_RETRIEVING_TENANT_ACTIVATED_CURRENCIES')
            );

        $response = $this->activatedCurrenciesController->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * Mock an object
    *
    * @param string name
    *
    * @return Mockery
    */
    private function mock($class)
    {
        return Mockery::mock($class);
    }
}