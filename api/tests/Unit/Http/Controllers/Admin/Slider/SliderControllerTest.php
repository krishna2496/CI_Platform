<?php

namespace Tests\Unit\Http\Controllers\Admin\Slider;

use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Helpers\S3Helper;
use App\Http\Controllers\Admin\Slider\SliderController;
use App\Models\Slider;
use App\Repositories\Slider\SliderRepository;
use Exception;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator as TrueValidator;
use Mockery;
use TestCase;
use Validator;

class SliderControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->generateMocks();
    }

    public function testIndexWithSliders()
    {
        $sliders = [new Slider];
        $collection = new Collection($sliders);

        $this->sliderRepository
            ->expects($this->once())
            ->method('getSliders')
            ->willReturn($collection);

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->index($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertSame($collection->toArray(), $data['data']);
    }

    public function testIndexWithNoSliders()
    {
        $sliders = [];
        $collection = new Collection($sliders);

        $this->sliderRepository
            ->expects($this->once())
            ->method('getSliders')
            ->willReturn($collection);

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->index($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function testStoreSuccess()
    {
        $this->withoutEvents();

        $url = $this->faker->url;
        $this->request->query->add(['url' => $url]);

        $this->validator
            ->expects($this->once())
            ->method('fails')
            ->willReturn(false);

        $limit = config('constants.SLIDER_LIMIT');
        $this->sliderRepository
            ->expects($this->once())
            ->method('getAllSliderCount')
            ->willReturn($limit - 1);  // 1 less than the limit

        $this->sliderRepository
            ->expects($this->once())
            ->method('storeSlider')
            ->willReturn($this->slider);

        $tenantName = 'Tenant 1';
        $this->helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest')
            ->with($this->request)
            ->willReturn($tenantName);

        $imageUrl = $this->faker->url;
        $this->s3Helper
            ->expects($this->once())
            ->method('uploadFileOnS3Bucket')
            ->with($url, $tenantName)
            ->willReturn($imageUrl);

        $this->sliderRepository
            ->expects($this->once())
            ->method('updateSlider')
            ->with(
                ['url' => $imageUrl],
                $this->slider->slider_id
            );

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->store($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(201, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertSame(['slider_id' => $this->slider->slider_id], $data['data']);
    }

    public function testStoreFailedValidation()
    {
        $this->validator
            ->expects($this->once())
            ->method('fails')
            ->willReturn(true);

        $this->messageBag
            ->expects($this->once())
            ->method('first')
            ->willReturn('validation error');

        $this->validator
            ->expects($this->once())
            ->method('errors')
            ->willReturn($this->messageBag);

        $this->sliderRepository
            ->expects($this->never())
            ->method('getAllSliderCount');

        $this->sliderRepository
            ->expects($this->never())
            ->method('storeSlider');

        $this->helpers
            ->expects($this->never())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->never())
            ->method('uploadFileOnS3Bucket');

        $this->sliderRepository
            ->expects($this->never())
            ->method('updateSlider');

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->store($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function testStoreReachedLimit()
    {
        $this->validator
            ->expects($this->once())
            ->method('fails')
            ->willReturn(false);

        $limit = config('constants.SLIDER_LIMIT');
        $this->sliderRepository
            ->expects($this->once())
            ->method('getAllSliderCount')
            ->willReturn($limit + 1);  // 1 more than the limit

        $this->sliderRepository
            ->expects($this->never())
            ->method('storeSlider');

        $this->helpers
            ->expects($this->never())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->never())
            ->method('uploadFileOnS3Bucket');

        $this->sliderRepository
            ->expects($this->never())
            ->method('updateSlider');

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->store($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(403, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function testUpdateSuccess()
    {
        $this->withoutEvents();

        $sliderId = 1;
        $url = $this->faker->url;
        $this->request->query->add(['url' => $url]);

        $this->validator
            ->expects($this->once())
            ->method('fails')
            ->willReturn(false);

        $this->sliderRepository
            ->expects($this->once())
            ->method('find')
            ->with($sliderId);

        $tenantName = 'Tenant 1';
        $this->helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest')
            ->with($this->request)
            ->willReturn($tenantName);

        $imageUrl = $this->faker->url;
        $this->s3Helper
            ->expects($this->once())
            ->method('uploadFileOnS3Bucket')
            ->with($imageUrl, $tenantName)
            ->willReturn($imageUrl);

        $this->request->merge(['url' => $imageUrl]);
        $this->sliderRepository
            ->expects($this->once())
            ->method('updateSlider')
            ->with($this->request->toArray(), $sliderId)
            ->willReturn(true);

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->update($this->request, $sliderId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function testUpdateFailedValidation()
    {
        $this->withoutEvents();

        $sliderId = 1;
        $url = $this->faker->url;
        $this->request->query->add(['url' => $url]);

        $this->validator
            ->expects($this->once())
            ->method('fails')
            ->willReturn(true);

        $this->messageBag
            ->expects($this->once())
            ->method('first')
            ->willReturn('validation error');

        $this->validator
            ->expects($this->once())
            ->method('errors')
            ->willReturn($this->messageBag);

        $this->sliderRepository
            ->expects($this->never())
            ->method('find');

        $this->helpers
            ->expects($this->never())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->never())
            ->method('uploadFileOnS3Bucket');

        $this->sliderRepository
            ->expects($this->never())
            ->method('updateSlider');

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->update($this->request, $sliderId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function testUpdateNotFound()
    {
        $this->withoutEvents();

        $sliderId = 1;
        $url = $this->faker->url;
        $this->request->query->add(['url' => $url]);

        $this->validator
            ->expects($this->once())
            ->method('fails')
            ->willReturn(false);

        $this->sliderRepository
            ->expects($this->once())
            ->method('find')
            ->willThrowException(new ModelNotFoundException);

        $this->helpers
            ->expects($this->never())
            ->method('getSubDomainFromRequest');

        $this->s3Helper
            ->expects($this->never())
            ->method('uploadFileOnS3Bucket');

        $this->sliderRepository
            ->expects($this->never())
            ->method('updateSlider');

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->update($this->request, $sliderId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function testUpdateUnknownError()
    {
        $this->withoutEvents();

        $sliderId = 1;
        $url = $this->faker->url;
        $this->request->query->add(['url' => $url]);

        $this->validator
            ->expects($this->once())
            ->method('fails')
            ->willReturn(false);

        $this->sliderRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($this->slider);

        $tenantName = 'Tenant 1';
        $this->helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest')
            ->with($this->request)
            ->willReturn($tenantName);

        $this->s3Helper
            ->expects($this->once())
            ->method('uploadFileOnS3Bucket')
            ->with($url, $tenantName)
            ->willThrowException(new Exception);

        $this->sliderRepository
            ->expects($this->never())
            ->method('updateSlider');

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->update($this->request, $sliderId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function testDestroySuccess()
    {
        $this->withoutEvents();

        $sliderId = 1;

        $this->sliderRepository
            ->expects($this->once())
            ->method('delete')
            ->with($sliderId)
            ->willReturn(true);

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->destroy($sliderId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(204, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function testDestroyNotFound()
    {
        $this->withoutEvents();

        $sliderId = 1;

        $this->sliderRepository
            ->expects($this->once())
            ->method('delete')
            ->with($sliderId)
            ->willThrowException(new ModelNotFoundException);

        $sliderController = $this->getSliderControllerMock();
        $response = $sliderController->destroy($sliderId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('data', $data);
    }

    private function getSliderControllerMock()
    {
        Validator::shouldReceive('make')
            ->andReturn($this->validator);
        return new SliderController(
            $this->sliderRepository,
            new ResponseHelper,
            $this->helpers,
            $this->s3Helper,
            $this->request
        );
    }

    private function generateMocks()
    {
        $this->helpers = $this->createMock(Helpers::class);
        $this->messageBag = $this->createMock(MessageBag::class);
        $this->request = new Request;
        $this->request->header('php-auth-user', 'test-api-key');
        $this->s3Helper = $this->createMock(S3Helper::class);
        $this->slider = new Slider;
        $this->slider->slider_id = 1;
        $this->sliderRepository = $this->createMock(SliderRepository::class);
        $this->validator = $this->createMock(TrueValidator::class);
    }
}
