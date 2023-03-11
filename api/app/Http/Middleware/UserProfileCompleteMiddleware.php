<?php
namespace App\Http\Middleware;

use Closure;
use App\Traits\RestExceptionHandlerTrait;
use App\User;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;

class UserProfileCompleteMiddleware
{
    use RestExceptionHandlerTrait;
    
     /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new middleware instance.
     *
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(
        ResponseHelper $responseHelper
    ) {
        $this->responseHelper = $responseHelper;
    }

    /**
     * Handle an incoming request.
     *
     * @param object $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = User::find($request->auth->user_id);
        
        if ($user->is_profile_complete === '0') {
            return $this->responseHelper->error(
                Response::HTTP_UNAUTHORIZED,
                Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                config('constants.error_codes.ERROR_UNAUTHORIZED_USER'),
                trans('messages.custom_error_message.ERROR_UNAUTHORIZED_USER')
            );
        }
        return $next($request);
    }
}
