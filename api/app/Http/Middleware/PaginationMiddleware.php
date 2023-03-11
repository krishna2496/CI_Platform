<?php
namespace App\Http\Middleware;

use Closure;

class PaginationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  object  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		if ($request->perPage === 'all') {
			$request->perPage = config('constants.PER_PAGE_ALL');
		} else if ($request->perPage > config('constants.PER_PAGE_MAX')
        || !is_numeric($request->perPage) && isset($request->perPage)) {
            $request->perPage = config('constants.PER_PAGE_MAX');
        }
        $request->merge(['perPage' => $request->get('perPage', config('constants.PER_PAGE_LIMIT'))]);
        return $next($request);
    }
}
