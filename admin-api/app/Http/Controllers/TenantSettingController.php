<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\TenantSetting\TenantSettingRepository;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;

//!  Tenant setting controller
/*!
This controller is responsible for handling tenant setting listing operation.
 */
class TenantSettingController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\TenantSetting\TenantSettingRepository
     */
    private $tenantSettingRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;


    /**
     * Create a new Tenant controller instance.
     *
     * @param  App\Repositories\TenantSetting\TenantSettingRepository $tenantSettingRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(
        TenantSettingRepository $tenantSettingRepository,
        ResponseHelper $responseHelper
    ) {
        $this->tenantSettingRepository = $tenantSettingRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = $this->tenantSettingRepository->getAllSettings();

        $responseMessage = ($settings->count() > 0) ? trans('messages.success.MESSAGE_ALL_SETTING_LISTING') :
        trans('messages.success.MESSAGE_NO_RECORD_FOUND');

        return $this->responseHelper->success(Response::HTTP_OK, $responseMessage, $settings->toArray());
    }
}
