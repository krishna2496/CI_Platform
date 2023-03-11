<?php
namespace App\Http\Middleware;

use Closure;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check header request and determine localizaton
        $local = ($request->hasHeader('X-localization')) ? $request->header('X-localization') : env('TENANT_DEFAULT_LANGUAGE_CODE');
        // set laravel localization
        config(['app.locale' => $local]);
        // continue request
        return $next($request);
    }
}
