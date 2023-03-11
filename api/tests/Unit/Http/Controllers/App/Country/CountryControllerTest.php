<?php

namespace Tests\Unit\Http\Controllers\App\Country;

use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\App\Country\CountryController;
use App\Repositories\Country\CountryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use TestCase;

class CountryControllerTest extends TestCase
{
    /**
     * @var App\Repositories\Country\CountryRepository
     */
    private $countryRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Http\Controllers\App\Country\CountryController
     */
    private $countryController;

    public function setUp(): void
    {
        parent::setUp();
        $this->countryRepository = $this->mock(CountryRepository::class);
        $this->responseHelper = $this->mock(ResponseHelper::class);
        $this->languageHelper = $this->mock(LanguageHelper::class);

        $this->countryController = new CountryController(
            $this->countryRepository,
            $this->responseHelper,
            $this->languageHelper
        );
    }

    /**
     * Test index method on CountryController class Not Detailed
     * @testdox void
     */
    public function testIndexNotDetailed()
    {
        $request = new Request();
        $countries = $this->getCountries();
        $languageId = 1;
        $defaultTenantLanguage = (object) [
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $this->languageHelper
            ->shouldReceive('getLanguageId')
            ->once()
            ->with($request)
            ->andReturn($languageId);

        $this->countryRepository
            ->shouldReceive('countryList')
            ->once()
            ->andReturn($countries);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($request)
            ->andReturn($defaultTenantLanguage);

        $expected = [
            29 => 'Philippines',
            320 => 'Japan'
        ];

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_COUNTRY_LISTING'),
                $expected
            );

        $response = $this->countryController->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test index method on CountryController class Detailed
     * @testdox void
     */
    public function testIndexDetailed()
    {
        $request = new Request();
        $request->query->add([
            'detailed' => true
        ]);
        $countries = $this->getCountries();
        $languageId = 1;
        $defaultTenantLanguage = (object) [
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $this->languageHelper
            ->shouldReceive('getLanguageId')
            ->once()
            ->with($request)
            ->andReturn($languageId);

        $this->countryRepository
            ->shouldReceive('countryList')
            ->once()
            ->andReturn($countries);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($request)
            ->andReturn($defaultTenantLanguage);

        $expected = [
            [
                'id' => 29,
                'code' => 'PH',
                'name' => 'Philippines'
            ],
            [
                'id' => 320,
                'code' => 'JP',
                'name' => 'Japan'
            ]
        ];

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_COUNTRY_LISTING'),
                $expected
            );

        $response = $this->countryController->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test index method on CountryController class Empty
     * @testdox void
     */
    public function testIndexEmpty()
    {
        $request = new Request();
        $countries = new Collection([]);
        $languageId = 1;
        $defaultTenantLanguage = (object) [
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $this->languageHelper
            ->shouldReceive('getLanguageId')
            ->once()
            ->with($request)
            ->andReturn($languageId);

        $this->countryRepository
            ->shouldReceive('countryList')
            ->once()
            ->andReturn($countries);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($request)
            ->andReturn($defaultTenantLanguage);

        $expected = [];

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_NO_COUNTRY_FOUND'),
                $expected
            );

        $response = $this->countryController->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    private function getCountries()
    {
        return new Collection([
            [
                'country_id' => 29,
                'ISO' => 'PH',
                'languages' => [
                    [
                        'country_language_id' => 23,
                        'country_id' => 29,
                        'language_id' => 1,
                        'name' => 'Philippines'
                    ]
                ]
            ],
            [
                'country_id' => 320,
                'ISO' => 'JP',
                'languages' => [
                    [
                        'country_language_id' => 232,
                        'country_id' => 320,
                        'language_id' => 1,
                        'name' => 'Japan'
                    ]
                ]
            ]
        ]);
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
