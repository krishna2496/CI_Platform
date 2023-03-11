<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CorsMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		$headers = [
			'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE, PATCH',
			'Access-Control-Allow-Credentials' => 'true',
			'Access-Control-Max-Age'           => '86400',
			'Access-Control-Allow-Headers' 	   => 'Access-Control-Allow-Origin, Access-Control-allow-Headers, Token, Content-Type, Authorization, X-Requested-With, X-Localization',
            'Access-Control-Allow-Origin'      => $request->headers->get('origin'),
			'Access-Control-Expose-Headers'    => 'Token',
		];

		if ($request->isMethod('OPTIONS'))
		{
			return response()->json('{"method":"OPTIONS"}', 200, $headers);
		}

		$response = $next($request);

		if (get_class($response) === 'Symfony\Component\HttpFoundation\BinaryFileResponse') {
		    foreach($headers as $key => $value) {
			    $response->headers->set($key, $value);
		    }
		    return $response;
        }

		foreach($headers as $key => $value) {
			$response->header($key, $value);
		}

		return $response;
	}
}
