<?php
namespace App\Http\Middleware;

use App\Factories\JWTCookieFactory;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Repositories\Timezone\TimezoneRepository;
use App\User;
use Closure;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JwtMiddleware
{
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Repositories\Timezone\TimezoneRepository
     */
    private $timezoneRepository;

    /**
     * @var App\Helpers\Helpers $helpers
     */
    private $helpers;

    /**
     * Create a new middleware instance.
     *
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param App\Repositories\Timezone\TimezoneRepository $timezoneRepository
     * @param App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(
        ResponseHelper $responseHelper,
        TimezoneRepository $timezoneRepository,
        Helpers $helpers
    ) {
        $this->responseHelper = $responseHelper;
        $this->timezoneRepository = $timezoneRepository;
        $this->helpers = $helpers;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $token = ($request->hasCookie(JWTCookieFactory::COOKIE_NAME))
            ? $request->cookie(JWTCookieFactory::COOKIE_NAME)
            : '';

        if (!$token) {
            // Unauthorized response if token not there
            return $this->responseHelper->error(
                Response::HTTP_UNAUTHORIZED,
                Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                config('constants.error_codes.ERROR_TOKEN_NOT_PROVIDED'),
                trans('messages.custom_error_message.ERROR_TOKEN_NOT_PROVIDED')
            );
        }
        try {
            $credentials = $this->helpers->decodeJwtToken($token);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->responseHelper->error(
                Response::HTTP_UNAUTHORIZED,
                Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                config('constants.error_codes.ERROR_TOKEN_EXPIRED'),
                trans('messages.custom_error_message.ERROR_TOKEN_EXPIRED')
            );
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return $this->responseHelper->error(
                Response::HTTP_UNAUTHORIZED,
                Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                config('constants.error_codes.ERROR_IN_TOKEN_DECODE'),
                trans('messages.custom_error_message.ERROR_IN_TOKEN_DECODE')
            );
        } catch (\UnexpectedValueException $e) {
            return $this->responseHelper->error(
                Response::HTTP_BAD_REQUEST,
                Response::$statusTexts[Response::HTTP_BAD_REQUEST ],
                config('constants.error_codes.ERROR_IN_TOKEN_DECODE'),
                trans('messages.custom_error_message.ERROR_IN_TOKEN_DECODE')
            );
        }
        // Here we need to check tenant name from token and request
        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        if ($tenantName !== $credentials->fqdn) {
            return $this->responseHelper->error(
                Response::HTTP_UNAUTHORIZED,
                Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                config('constants.error_codes.ERROR_UNAUTHORIZED_USER'),
                trans('messages.custom_error_message.ERROR_UNAUTHORIZED_USER')
            );
        }
        $user = User::find($credentials->sub);
        if ($user) {
            if ($user->status !== config('constants.user_statuses.ACTIVE')) {
                return $this->responseHelper->error(
                    Response::HTTP_FORBIDDEN,
                    Response::$statusTexts[Response::HTTP_FORBIDDEN],
                    config('constants.error_codes.ERROR_USER_BLOCKED'),
                    trans('messages.custom_error_message.ERROR_USER_BLOCKED')
                );
            }

            if ($user->expiry) {
                $userExpirationDate = new DateTime($user->expiry);
                if ($userExpirationDate < new DateTime()) {
                    return $this->responseHelper->error(
                        Response::HTTP_FORBIDDEN,
                        Response::$statusTexts[Response::HTTP_FORBIDDEN],
                        config('constants.error_codes.ERROR_USER_EXPIRED'),
                        trans('messages.custom_error_message.ERROR_USER_EXPIRED')
                    );
                }
            }

            $timezone = $this->timezoneRepository->timezoneList($user->timezone_id);
            if ($timezone) {
                $timezone = $timezone->timezone;
            }
            config(['constants.TIMEZONE' => $timezone]);
            $request->auth = $user;
            return $next($request);
        } else {
            return $this->responseHelper->error(
                Response::HTTP_UNAUTHORIZED,
                Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                config('constants.error_codes.ERROR_UNAUTHORIZED_USER'),
                trans('messages.custom_error_message.ERROR_UNAUTHORIZED_USER')
            );
        }
    }
}
