<?php
namespace App\Http\Controllers\Admin\Donation;

use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Donation\DonationService;
use App\Services\DonationIp\WhitelistService;
use App\Traits\RestExceptionHandlerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class DonationController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var DonationService
     */
    private $donationService;

    /**
     * Creates a new Donation controller instance
     *
     * @param DonationService $donationService
     * @param ResponseHelper $responseHelper
     *
     * @return void
     */
    public function __construct(
        Helpers $helpers,
        ResponseHelper $responseHelper,
        DonationService $donationService
    ) {
        $this->helpers = $helpers;
        $this->responseHelper = $responseHelper;
        $this->donationService = $donationService;
    }

    /**
     * Get mission, organization and donation statistics
     *
     * @param Request $request Array of date ranges. Format: ['Y-M-D:Y-M-D','Y-M-D:Y-M-D']
     *
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        // Validate given query parameters
        $validator = Validator::make(
            $request->all(),
            [
                'date_ranges' => 'array',
                'date_ranges.*' => 'string|date_range'
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_DONATION_STATISTICS_PARAMS_DATA'),
                $validator->errors()->first()
            );
        }

        try {
            // Get current tenant currency
            $currency = $this->helpers
                ->getTenantActivatedCurrencies($request)
                ->where('default', 1)
                ->first();

            $stats = $this->donationService->getStatistics(
                $request->get('date_ranges', []),
                $currency ? $currency->code : null
            );

            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_DONATION_STATISTICS_RETRIEVED'),
                $stats
            );
        } catch (Exception $e) {
            return $this->responseHelper->error(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                config('constants.error_codes.ERROR_FAILED_RETRIEVING_STATISTICS'),
                'Failed to retrieve donation statistics.'
            );
        }
    }

}
