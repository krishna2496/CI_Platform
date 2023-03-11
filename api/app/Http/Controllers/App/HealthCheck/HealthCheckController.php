<?php

namespace App\Http\Controllers\App\HealthCheck;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;

class HealthCheckController
{
    protected $helpers;

    public function __construct(ResponseHelper $helpers)
    {
        $this->helpers = $helpers;
    }

    public function index()
    {
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('Health check successful');

        return $this->helpers->success($apiStatus, $apiMessage);
    }
}
