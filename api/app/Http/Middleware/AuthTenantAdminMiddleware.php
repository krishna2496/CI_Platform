<?php
namespace App\Http\Middleware;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use DB;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Support\Facades\Hash;

class AuthTenantAdminMiddleware
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * Create a new middleware instance.
     *
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(ResponseHelper $responseHelper, Helpers $helpers)
    {
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
        $this->db = app()->make('db');
    }
 
    /**
     * Handle an incoming request.
     *
     * @param   $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check basic auth passed or not
        if ($request->header('php-auth-user') === null && $request->header('php-auth-pw') === null
            || (empty($request->header('php-auth-user')) && empty($request->header('php-auth-pw')))
        ) {
            return $this->responseHelper->error(
                Response::HTTP_UNAUTHORIZED,
                Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                config('constants.error_codes.ERROR_API_AND_SECRET_KEY_REQUIRED'),
                trans('messages.custom_error_message.ERROR_API_AND_SECRET_KEY_REQUIRED')
            );
        }
        // authenticate api user based on basic auth parameters
        $apiUser = $this->db->table('api_user')
                    ->where('api_key', base64_encode($request->header('php-auth-user')))
                    ->where('status', '1')
                    ->whereNull('deleted_at')
                    ->first();
        
        // If user authenticates successfully
        if ($apiUser && Hash::check($request->header('php-auth-pw'), $apiUser->api_secret)) {
            // Create connection with their tenant database
            $this->helpers->createConnection($apiUser->tenant_id);
            return $next($request);
        }
        // Send authentication error response if api user not found in master database
        return $this->responseHelper->error(
            Response::HTTP_UNAUTHORIZED,
            Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
            config('constants.error_codes.ERROR_INVALID_API_AND_SECRET_KEY'),
            trans('messages.custom_error_message.ERROR_INVALID_API_AND_SECRET_KEY')
        );
    }
}
