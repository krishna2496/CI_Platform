<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequestsMiddlewareTest extends \TestCase
{
    /**
     * @var RateLimiter
     */
    private $limiter;

    /**
     * @var ResponseHelper
     */
    private $responseHelper;

    /**
     * @var ThrottleRequestsMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->responseHelper = $this->createMock(ResponseHelper::class);
        $this->limiter = $this->createMock(RateLimiter::class);

        $this->middleware = new ThrottleRequestsMiddleware(
            $this->limiter,
            $this->responseHelper
        );
    }

    public function testHandle()
    {
        $request = new Request([],[],[],[],[],['SERVER_NAME' => 'myServer']);
        $request->setMethod('GET');
        $next = \Closure::fromCallable(function ($request) { return new Response(Response::HTTP_OK); });

        $this->limiter
            ->expects($this->at(0))
            ->method('tooManyAttempts')
            ->willReturn(false);

        $this->limiter
            ->expects($this->at(1))
            ->method('hit')
            ->willReturn(1);

        $this->limiter
            ->expects($this->at(2))
            ->method('retriesLeft')
            ->willReturn(4);

        $response = $this->middleware->handle($request, $next, 5, 1);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('x-ratelimit-remaining', $response->headers->all());
        $this->assertEquals([4], $response->headers->all()['x-ratelimit-remaining']);
        $this->assertArrayHasKey('x-ratelimit-limit', $response->headers->all());
        $this->assertEquals([5], $response->headers->all()['x-ratelimit-limit']);
        $this->assertArrayNotHasKey('retry-after', $response->headers->all());
        $this->assertArrayNotHasKey('x-ratelimit-reset', $response->headers->all());

    }

    public function testHandleTooManyAttempts()
    {
        $request = new Request([],[],[],[],[],['SERVER_NAME' => 'myServer']);
        $request->setMethod('GET');
        $next = \Closure::fromCallable(function ($request) { return new Response(); });

        $this->limiter
            ->expects($this->at(0))
            ->method('tooManyAttempts')
            ->willReturn(true);

        $this->limiter
            ->expects($this->at(1))
            ->method('availableIn')
            ->willReturn(60);

        $this->responseHelper
            ->expects($this->once())
            ->method('error')
            ->with(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_MAX_ATTEMPTS_REACHED'),
                trans('auth.throttle', ['seconds' => 60])
            )
            ->willReturn(new JsonResponse([], Response::HTTP_FORBIDDEN));

        $response = $this->middleware->handle($request, $next, 5, 1);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertArrayHasKey('x-ratelimit-remaining', $response->headers->all());
        $this->assertArrayHasKey('x-ratelimit-limit', $response->headers->all());
        $this->assertArrayHasKey('retry-after', $response->headers->all());
        $this->assertArrayHasKey('x-ratelimit-reset', $response->headers->all());

    }
}
