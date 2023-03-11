<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Config;
use App\Helpers\Helpers;
use Closure;
use DB;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\TenantDomainNotFoundException;

class TenantConnectionMiddleware
{
    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * Create a new middleware instance.
     *
     * @param App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(Helpers $helpers)
    {
        $this->helpers = $helpers;
        $this->db = app()->make('db');
    }

    /**
     * Handle an incoming request.
     *
     * @param object $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $domain = $this->helpers->getSubDomainFromRequest($request);
        $this->helpers->switchDatabaseConnection('mysql');
        $tenant = $this->db->table('tenant')->select('tenant_id')
        ->where('name', $domain)->whereNull('deleted_at')->first();
        
        if (!$tenant) {
            throw new TenantDomainNotFoundException(
                trans('messages.custom_error_message.ERROR_TENANT_DOMAIN_NOT_FOUND'),
                config('constants.error_codes.ERROR_TENANT_DOMAIN_NOT_FOUND')
            );
        }
        $this->helpers->createConnection($tenant->tenant_id);
        return $next($request);
    }
}
