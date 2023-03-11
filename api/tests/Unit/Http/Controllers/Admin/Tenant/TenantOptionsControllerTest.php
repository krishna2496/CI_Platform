<?php

namespace Tests\Unit\Http\Controllers\Admin\Tenant;

use TestCase;
use Mockery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use App\Helpers\ResponseHelper;
use App\Helpers\S3Helper;
use App\Helpers\Helpers;
use App\Http\Controllers\Admin\Tenant\TenantOptionsController;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Services\CustomStyling\CustomStylingService;

class TenantOptionsControllerTest extends TestCase
{
    const OPTION_NAME_SSO = 'saml_settings';

    /**
    * @testdox Test fetchTenantOptionValue success
    *
    * @return void
    */
    public function testFetchTenantOptionValue_001()
    {
        $helpers = $this->mock(Helpers::class);
        $s3helper = $this->mock(S3Helper::class);
        $customStylingService = $this->mock(CustomStylingService::class);

        $samlSettings = new Collection([
            'option_name' => self::OPTION_NAME_SSO,
            'option_value' => [
                'last_name' => 'Rimando',
                'first_name' => 'Allan Paul'
            ]
        ]);

        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);
        $tenantOptionRepository->shouldReceive('getOptionValue')
            ->with(self::OPTION_NAME_SSO)
            ->once()
            ->andReturn($samlSettings);

        $responseHelper = $this->app->make(ResponseHelper::class);

        $request = $this->getMockRequest(
            'GET',
            '',
            '/happy',
            [],
            $samlSettings->toArray()
        );

        $response = $this->getController(
            $tenantOptionRepository,
            $responseHelper,
            $helpers,
            $s3helper,
            $customStylingService,
            $request
        )
        ->fetchTenantOptionValue($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            json_decode($response->getContent(), true),
            [
                'status' => Response::HTTP_OK,
                'data' => $samlSettings->toArray(),
                'message' => trans('messages.success.MESSAGE_TENANT_OPTION_FOUND')
            ]
        );
    }

    /**
    * @testdox Test fetchTenantOptionValue failure, validation failed
    *
    * @return void
    */
    public function testFetchTenantOptionValue_002()
    {
        $helpers = $this->mock(Helpers::class);
        $s3helper = $this->mock(S3Helper::class);
        $customStylingService = $this->mock(CustomStylingService::class);

        $samlSettings = new Collection([]);

        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);
        $tenantOptionRepository->shouldReceive('getOptionValue')
            ->with(self::OPTION_NAME_SSO)
            ->never()
            ->andReturn($samlSettings);

        $responseHelper = $this->app->make(ResponseHelper::class);

        $request = $this->getMockRequest(
            'GET',
            '',
            '/happy',
            [],
            $samlSettings->toArray()
        );

        $response = $this->getController(
            $tenantOptionRepository,
            $responseHelper,
            $helpers,
            $s3helper,
            $customStylingService,
            $request
        )
        ->fetchTenantOptionValue($request);

        $responseData = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($responseData['errors'][0], [
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'type' => Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
            'code' => (int) config('constants.error_codes.ERROR_TENANT_OPTION_REQUIRED_FIELDS_EMPTY'),
            'message' => 'The option name field is required.'
        ]);
    }

    /**
    * @testdox Test fetchTenantOptionValue failure, no tenant option found
    *
    * @return void
    */
    public function testFetchTenantOptionValue_003()
    {
        $helpers = $this->mock(Helpers::class);
        $s3helper = $this->mock(S3Helper::class);
        $customStylingService = $this->mock(CustomStylingService::class);

        $samlSettings = new Collection([]);

        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);
        $tenantOptionRepository->shouldReceive('getOptionValue')
            ->with(self::OPTION_NAME_SSO)
            ->once()
            ->andReturn($samlSettings);

        $responseHelper = $this->app->make(ResponseHelper::class);

        $request = $this->getMockRequest(
            'GET',
            '',
            '/happy',
            [],
            ['option_name' => self::OPTION_NAME_SSO]
        );

        $response = $this->getController(
            $tenantOptionRepository,
            $responseHelper,
            $helpers,
            $s3helper,
            $customStylingService,
            $request
        )
        ->fetchTenantOptionValue($request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            json_decode($response->getContent(), true),
            [
                'status' => Response::HTTP_OK,
                'message' => trans('messages.custom_error_message.ERROR_TENANT_OPTION_NOT_FOUND')
            ]
        );
    }

    /**
     * Create a new controller instance.
     *
     * @param  App\Repositories\TenantOption\TenantOptionRepository $tenantOptionRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param  App\Helpers\Helpers $helpers
     * @param  App\Helpers\S3Helper $s3helper
     * @param  App\Services\CustomStyling\CustomStylingService $customStylingService
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    private function getController(
        TenantOptionRepository $tenantOptionRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers,
        S3Helper $s3helper,
        CustomStylingService $customStylingService,
        Request $request
    ) {
        return new TenantOptionsController(
            $tenantOptionRepository,
            $responseHelper,
            $helpers,
            $s3helper,
            $customStylingService,
            $request
        );
    }

    /**
    * Mock a request
    *
    * @param string method
    * @param string $content
    * @param string $uri
    * @param array $server
    * @param array $parameters
    * @param array $cookies
    * @param array $files
    *
    * @return \Illuminate\Http\Request
    */
    private function getMockRequest(
        $method,
        $content,
        $uri = '/test',
        $server = [
        'Content-Type' => 'application/json'
        ],
        $parameters = [],
        $cookies = [],
        $files = []
    ) {
        $request = new Request();
        return $request->createFromBase(
            SymfonyRequest::create(
                $uri,
                $method,
                $parameters,
                $cookies,
                $files,
                $server,
                $content
            )
        );
    }

    /**
    * Mock an object
    *
    * @param string name
    *
    * @return Mockery
    */
    private function mock($name)
    {
        return Mockery::mock($name);
    }
}
