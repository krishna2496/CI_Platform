<?php
namespace App\Http\Controllers\App\Timezone;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Repositories\Timezone\TimezoneRepository;
use App\Traits\RestExceptionHandlerTrait;
use InvalidArgumentException;

//!  Timezone controller
/*!
This controller is responsible for handling timezone listing operation.
 */
class TimezoneController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\Timezone\TimezoneRepository
     */
    private $timeZoneRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\Timezone\TimezoneRepository $timeZoneRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(
        TimezoneRepository $timeZoneRepository,
        ResponseHelper $responseHelper
    ) {
        $this->timeZoneRepository = $timeZoneRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
    * Get timezone list
    *
    * @return Illuminate\Http\JsonResponse
    */
    public function index() : JsonResponse
    {
        $timezoneList = $this->timeZoneRepository->getTimezoneList();
        $apiData = $timezoneList->toArray();
        $apiStatus = Response::HTTP_OK;
        $apiMessage = (!empty($apiData)) ?
        trans('messages.success.MESSAGE_TIMEZONE_LISTING') :
        trans('messages.success.MESSAGE_NO_TIMEZONE_FOUND');
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
