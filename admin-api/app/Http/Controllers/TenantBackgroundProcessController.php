<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\Tenant\TenantRepository;
use App\Jobs\TenantBackgroundJobsJob;
use App\Traits\RestExceptionHandlerTrait;
use App\Helpers\ResponseHelper;

//!  Tenantbackgroundprocess controller
/*!
This controller is responsible for handling tenant create background operation.
 */
class TenantBackgroundProcessController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\Tenant\TenantRepository
     */
    private $tenantRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new TenantBackgroundProcess controller instance.
     *
     * @param App\Repositories\Tenant\TenantRepository $tenantRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(TenantRepository $tenantRepository, ResponseHelper $responseHelper)
    {
        $this->tenantRepository = $tenantRepository;
        $this->responseHelper = $responseHelper;
    }
    
    /**
     * Cron Job : Run tenant's background jobs for tenant create
     *
     * @param  int $tenantId
     * @return void
     */
    public function runBackgroundProcess($tenantId = null)
    {
        $tenants = $this->tenantRepository->getPendingTenantsForProcess($tenantId);
        if ($tenants->count()) {
            foreach ($tenants as $tenant) {
                dispatch(new TenantBackgroundJobsJob($tenant));
            }
        }
        // Set response message
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_BACKGROUND_PROCESS_COMPLETED');
        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
