<?php

namespace Tests\Unit\Http\Middleware;

use App\Helpers\ResponseHelper;
use App\Http\Middleware\JsonApiMiddleware;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Mockery;
use TestCase;

class JsonApiMiddlewareTest extends TestCase
{
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Http\Middleware\JsonApiMiddleware
     */
    private $jsonApiMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->responseHelper = $this->mock(ResponseHelper::class);

        $this->jsonApiMiddleware = new JsonApiMiddleware(
            $this->responseHelper
        );
    }

    public function testHandleValidJson()
    {
        $data = json_encode([
            'attribute' => 'value'
        ]);
        $request = $this->createRequest($data);

        $this->responseHelper->shouldNotReceive('error');

        foreach (JsonApiMiddleware::PARSED_METHODS as $method) {
            $request->setMethod(Request::METHOD_POST);
            $this->jsonApiMiddleware->handle($request, function (){});
        }
    }

    public function testHandleInvalidJson()
    {
        $data = 'invalid';
        $request = $this->createRequest($data);
        $request->setMethod(Request::METHOD_POST);

        $this->responseHelper->shouldReceive('error')
            ->times(count(JsonApiMiddleware::PARSED_METHODS))
            ->with(
                Response::HTTP_BAD_REQUEST,
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_JSON')
            );

        foreach (JsonApiMiddleware::PARSED_METHODS as $method) {
            $request->setMethod(Request::METHOD_POST);
            $this->jsonApiMiddleware->handle($request, function (){});
        }
    }

    public function testHandleInvalidJsonGetMethod()
    {
        $data = 'invalid';
        $request = $this->createRequest($data);
        $request->setMethod(Request::METHOD_GET);

        $this->responseHelper->shouldNotReceive('error');

        $this->jsonApiMiddleware->handle($request, function (){});
    }

    /**
     * Mock an object
     *
     * @param string $class
     * @return MockInterface
     */
    private function mock($class)
    {
        return Mockery::mock($class);
    }

    /**
     * Mock an object
     *
     * @param string $content
     * @return Request
     */
    private function createRequest(string $content): Request
    {
        return new Request([], [], [], [], [], [], $content);
    }
}
