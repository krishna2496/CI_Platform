<?php

namespace App\Http\Middleware;

use Closure;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;

class TenantHasSettingsMiddleware
{
    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
    * Create a new Tenant has setting instance
    *
    * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
    * @param App\Helpers\ResponseHelper $responseHelper
    * @return void
    */
    public function __construct(
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        ResponseHelper $responseHelper
    ) {
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  array $settings
     * @return mixed
     */
    public function handle($request, Closure $next, ...$settings)
    {
        foreach ($settings as $key => $setting) {
            $result = $this->tenantActivatedSettingRepository->checkTenantSettingStatus(
                $setting,
                $request
            );
            if (!$result) {
                return $this->responseHelper->error(
                    Response::HTTP_FORBIDDEN,
                    Response::$statusTexts[Response::HTTP_FORBIDDEN],
                    config('constants.error_codes.ERROR_TENANT_SETTING_DISABLED'),
                    trans('messages.custom_error_message.ERROR_TENANT_SETTING_DISABLED')
                );
            }
        }
        $response = $next($request);
        return $response;
    }
}
