<?php

namespace Tests\Unit\Services\Donation;

use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\Donation\DonationController;
use App\Services\Donation\DonationService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use TestCase;
use Validator;

class DonationControllerTest extends TestCase
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
     * @var DonationService
     */
    private $donationService;

    /**
     * @var DonationController
     */
    private $donationController;

    public function setUp(): void
    {
        parent::setUp();

        $this->helpers = $this->mock(Helpers::class);
        $this->responseHelper = $this->mock(ResponseHelper::class);
        $this->donationService = $this->mock(DonationService::class);

        $this->donationController = new DonationController(
            $this->helpers,
            $this->responseHelper,
            $this->donationService
        );
    }

    /**
    * @testdox Test method statistics
    */
    public function testStatistics()
    {
        $data = [
            'date_ranges' => [
                '1970-01-01:1975-12-20'
            ]
        ];

        $requestData = new Request($data);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $expected = [
            'missions' => 'missionCount',
            'organizations' => 'organizationCount',
            'donations' => 'donationCount',
            'date_ranges' => [
                'start_datetime' => '1970-01-01',
                'end_datetime' => '1975-12-20',
                'total_missions' => 23661,
                'total_organizations' => 1,
                'total_donations' => 372309108198.81
            ]
        ];

        $currency = new Collection([
            (object) [
                'code' => 'CRC',
                'default' => 1,
            ]
        ]);

        $this->helpers
            ->shouldReceive('getTenantActivatedCurrencies')
            ->once()
            ->with($requestData)
            ->andReturn($currency);

        $this->donationService
            ->shouldReceive('getStatistics')
            ->once()
            ->with(
                $requestData->get('date_ranges'),
                $currency->first()->code
            )
            ->andReturn($expected);

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_DONATION_STATISTICS_RETRIEVED'),
                $expected
            )
            ->andReturn(new JsonResponse());

        $response = $this->donationController->statistics($requestData);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test method statistics with validation error
    */
    public function testStatisticsValidation()
    {
        $data = [
            'date_ranges' => [
                '1970-01-01:1975-12-20'
            ]
        ];

        $requestData = new Request($data);

        $errors = new Collection([
            config('constants.error_codes.ERROR_DONATION_STATISTICS_PARAMS_DATA')
        ]);
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_DONATION_STATISTICS_PARAMS_DATA'),
                $errors->first()
            );

        $this->donationService
            ->shouldReceive('getStatistics')
            ->never();

        $this->responseHelper
            ->shouldReceive('success')
            ->never();

        $response = $this->donationController->statistics($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test method statistics with validation error
    */
    public function testStatisticsException()
    {
        $data = [
            'date_ranges' => [
                '1970-01-01:1975-12-20'
            ]
        ];

        $requestData = new Request($data);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $currency = new Collection([
            (object) [
                'code' => 'CRC',
                'default' => 1,
            ]
        ]);

        $this->helpers
            ->shouldReceive('getTenantActivatedCurrencies')
            ->once()
            ->with($requestData)
            ->andReturn($currency);

        $this->donationService
            ->shouldReceive('getStatistics')
            ->once()
            ->with(
                $requestData->get('date_ranges'),
                $currency->first()->code
            )
            ->andThrow(new Exception);

        $this->responseHelper
            ->shouldReceive('success')
            ->never();

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                config('constants.error_codes.ERROR_FAILED_RETRIEVING_STATISTICS'),
                'Failed to retrieve donation statistics.'
            )
            ->andReturn(new JsonResponse());

        $response = $this->donationController->statistics($requestData);
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
