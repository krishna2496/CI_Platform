<?php
namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\IpUtils;

class RedirectInvalidIPs
{
    /**
     * @var string
     */
    private $validIP;

    /**
     * @var array
     */
    protected $ips = [];

    /**
     * Create a new RedirectInvalidIPs instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->validIP = env('VALID_IP_FOR_SECURE_API');
        array_push($this->ips, $this->validIP);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		if (env('APP_ENV')!='local' && env('APP_ENV')!='testing') {
			foreach ($request->getClientIps() as $ip) {
				if (!$this->isValidIp($ip)) {
					return redirect('/');
				}
			}
		}
        return $next($request);
    }

    /**
     * It will check passed ip is exist in ips array or not
     *
     * @param string $ip
     * @return bool
     */
    protected function isValidIp(string $ip): bool
    {
        return in_array($ip, $this->ips);
    }
}
