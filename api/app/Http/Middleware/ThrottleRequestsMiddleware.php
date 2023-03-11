<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Http\Response;

class ThrottleRequestsMiddleware
{
    use InteractsWithTime;

    /**
     * @var RateLimiter
     */
    protected $limiter;

    /**
     * @var ResponseHelper
     */
    protected $responseHelper;

    /**
     * Create a new request throttler.
     *
     * @param  RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter, ResponseHelper $responseHelper)
    {
        $this->limiter = $limiter;
        $this->responseHelper = $responseHelper;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return JsonResponse|Response
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        if ($request->hasHeader('disableThrottle')
            && env('DISABLE_THROTTLE_TOKEN', '') !== ''
            && $request->header('disableThrottle') === env('DISABLE_THROTTLE_TOKEN')
        ) {
            return $next($request);
        }

        $throttle = new \stdClass();
        $throttle->key = $this->resolveRequestSignature($request);
        $throttle->maxAttempts = $maxAttempts;
        $throttle->decayMinutes = $decayMinutes;
        $throttle->responseCallback = null;

        return $this->handleRequest($request, $next, $throttle);
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param \stdClass $limit
     * @return JsonResponse|Response
     */
    private function handleRequest(Request $request, Closure $next, \stdClass $limit)
    {
        try {
            if ($this->limiter->tooManyAttempts($limit->key, $limit->maxAttempts)) {
                throw new ThrottleRequestsException();
            }
            $this->limiter->hit($limit->key, $limit->decayMinutes * 60);

            $response = $next($request);

            return $this->addHeaders(
                $response,
                $limit->maxAttempts,
                $this->calculateRemainingAttempts($limit->key, $limit->maxAttempts)
            );

        } catch (ThrottleRequestsException $exception) {
            $retryAfter = $this->getTimeUntilNextRetry($limit->key);

            $response = $this->responseHelper
                ->error(
                    Response::HTTP_FORBIDDEN,
                    Response::$statusTexts[Response::HTTP_FORBIDDEN],
                    config('constants.error_codes.ERROR_MAX_ATTEMPTS_REACHED'),
                    trans('auth.throttle', ['seconds' => $retryAfter])
                );

            return $this->addHeaders(
                $response,
                $limit->maxAttempts,
                $this->calculateRemainingAttempts($limit->key, $limit->maxAttempts, $retryAfter),
                $retryAfter
            );
        }
    }

    /**
     * Resolve request signature.
     * @param Request $request
     * @return string
     */
    private function resolveRequestSignature(Request $request)
    {
        return sha1(
            $request->method() .
            '|' . $request->server('SERVER_NAME') .
            '|' . $request->path() .
            '|' . $request->ip()
        );
    }

    /**
     * Get the number of seconds until the next retry.
     *
     * @param  string $key
     * @return int
     */
    private function getTimeUntilNextRetry(string $key)
    {
        return $this->limiter->availableIn($key);
    }

    /**
     * Add the limit header information to the given response.
     *
     * @param JsonResponse|Response $response
     * @param int $maxAttempts
     * @param int $remainingAttempts
     * @param int|null $retryAfter
     * @return JsonResponse|Response
     */
    private function addHeaders($response, int $maxAttempts, int $remainingAttempts, int $retryAfter = null)
    {
        $response->headers->add(
            $this->getHeaders($maxAttempts, $remainingAttempts, $retryAfter, $response)
        );

        return $response;
    }

    /**
     * Get the limit headers information.
     *
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @param  int|null  $retryAfter
     * @param  JsonResponse|Response|null  $response
     * @return array
     */
    private function getHeaders(int $maxAttempts, int $remainingAttempts, int $retryAfter = null, $response = null): array
    {
        if ($response
            && !is_null($response->headers->get('X-RateLimit-Remaining'))
            &&  (int) $response->headers->get('X-RateLimit-Remaining') <= (int) $remainingAttempts) {
            return [];
        }

        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];

        if (!is_null($retryAfter)) {
            $headers['Retry-After'] = $retryAfter;
            $headers['X-RateLimit-Reset'] = $this->availableAt($retryAfter);
        }

        return $headers;
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  int|null  $retryAfter
     * @return int
     */
    private function calculateRemainingAttempts(string $key, int $maxAttempts, ?int $retryAfter = null): int
    {
        return is_null($retryAfter) ? $this->limiter->retriesLeft($key, $maxAttempts) : 0;
    }
}
