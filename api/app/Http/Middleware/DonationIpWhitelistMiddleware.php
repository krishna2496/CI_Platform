<?php

namespace App\Http\Middleware;

use App\Helpers\IPValidationHelper;
use App\Helpers\ResponseHelper;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Services\DonationIp\WhitelistService;
use App\Traits\RestExceptionHandlerTrait;
use Closure;
use Illuminate\Http\Request;

class DonationIpWhitelistMiddleware
{
    use RestExceptionHandlerTrait;

    const IP_WHITELIST_SETTING_KEY = 'donation_ip_whitelist';

    /**
     * @var App\Services\DonationIp\WhitelistService
     */
    private $whitelistService;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * @var App\Helpers\IPValidationHelper
     */
    private $ipValidationHelper;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Creates the middleware instance
     *
     * @param WhitelistService $whitelistService
     * @param Helpers $helpers
     * @param ResponseHelper $responseHelper
     *
     * @return void
     */
    public function __construct(
        WhitelistService $whitelistService,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        IPValidationHelper $ipValidationHelper,
        ResponseHelper $responseHelper
    ) {
        $this->whitelistService = $whitelistService;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->ipValidationHelper = $ipValidationHelper;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // check if tenant setting whitelist donation ip is activated
        $settingActivated = $this->tenantActivatedSettingRepository->checkTenantSettingStatus(
            self::IP_WHITELIST_SETTING_KEY,
            $request
        );

        if ($settingActivated) {
            $paginate = ['perPage' => null];
            $filters = ['search' => null, 'order' => null];

            $whitelistedIps = $this->whitelistService->getList(
                $paginate,
                $filters
            )->toArray();

            if (empty($whitelistedIps)) {
                return $this->forbidden(
                    config('constants.error_codes.ERROR_IP_ADDRESS_NOT_ALLOWED'),
                    trans('messages.custom_error_message.ERROR_IP_ADDRESS_NOT_ALLOWED')
                );
            }

            $whitelists = array_column($whitelistedIps, 'pattern');
            if (!$this->ipValidationHelper->verify($request->ip(), $whitelists)) {
                return $this->forbidden(
                    config('constants.error_codes.ERROR_IP_ADDRESS_NOT_ALLOWED'),
                    trans('messages.custom_error_message.ERROR_IP_ADDRESS_NOT_ALLOWED')
                );
            }
        }

        return $next($request);
    }
}