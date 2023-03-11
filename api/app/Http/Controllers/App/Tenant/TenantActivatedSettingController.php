<?php

namespace App\Http\Controllers\App\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\RestExceptionHandlerTrait;
use App\Models\TenantActivatedSetting;
use Validator;
use App\Helpers\Helpers;

//!  Tenant activated setting controller
/*!
This controller is responsible for handling tenant activated setting listing operation.
 */
class TenantActivatedSettingController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;
    


    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers
    ) {
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Fetch tenant all settings details
        $getTenantSettings = $this->helpers->getAllTenantSetting($request);
        
        // Fetch activated settings data
        $tenantSettings = $this->tenantActivatedSettingRepository->fetchAllTenantSettings();
        $tenantSettingData = array();

        if ($tenantSettings->count() &&  $getTenantSettings->count()) {
            foreach ($tenantSettings as $settingKey => $tenantSetting) {
                $index = $getTenantSettings->search(function ($value, $key) use ($tenantSetting) {
                    return $value->tenant_setting_id === $tenantSetting->settings->setting_id;
                });
                $tenantSettingData[$settingKey]['key'] = $getTenantSettings[$index]->key;
                $tenantSettingData[$settingKey]['tenant_setting_id'] = $getTenantSettings[$index]
                ->tenant_setting_id;
            }
        }
        $apiData = $tenantSettingData;

        // Set response data
        $apiStatus = Response::HTTP_OK;
        $apiMessage = ($tenantSettings->isEmpty() || $getTenantSettings->isEmpty())
        ? trans('messages.success.MESSAGE_NO_RECORD_FOUND') :
        trans('messages.success.MESSAGE_TENANT_SETTINGS_LISTING');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
