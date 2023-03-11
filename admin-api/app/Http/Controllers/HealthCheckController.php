<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;

class HealthCheckController extends Controller
{
    protected $responseHelper;

    public function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }

    public function index()
    {
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('Health check successful');

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
