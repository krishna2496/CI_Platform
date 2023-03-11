<?php
namespace App\Http\Controllers\Admin\DonationIp;

use App\Events\User\UserActivityLogEvent;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\DonationIpWhitelist;
use App\Services\DonationIp\WhitelistService;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class WhitelistController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var Array | All allowed order fields
     */
    const ORDER_FIELDS = [
        'pattern',
        'created_at'
    ];

    /**
     * @var Array | All allowed order directions
     */
    const ORDER_DIRECTIONS = [
        'desc',
        'asc'
    ];

    /**
     * @var WhitelistService
     */
    private $whitelistService;

    /**
     * @var ResponseHelper
     */
    private $responseHelper;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * Create a new controller instance.
     *
     * @param WhitelistService $whitelistService
     * @param ResponseHelper $responseHelper
     *
     * @return void
     */
    public function __construct(
        WhitelistService $whitelistService,
        ResponseHelper $responseHelper,
        Request $request
    ) {
        $this->whitelistService = $whitelistService;
        $this->responseHelper = $responseHelper;
        $this->userApiKey = $request->header('php-auth-user');
    }

    /**
     * Get Donation IP whitelist list
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function getList(Request $request): JsonResponse
    {
        $paginate = [
            'perPage' => $request->perPage
        ];
        $filters = [
            'search' => $request->get('search', null)
        ];

        $orderBy = $request->input('order.orderBy', null);
        if (in_array($orderBy, self::ORDER_FIELDS)) {
            $orderDir = $request->input('order.orderDir', null);
            $filters['order'][$orderBy] = in_array($orderDir, self::ORDER_DIRECTIONS) ? $orderDir : 'asc';
        } else {
            $filters['order']['created_at'] = 'desc';
        }

        $patterns = $this->whitelistService->getList($paginate, $filters);

        $message = $patterns->isEmpty() ?
            trans('messages.success.MESSAGE_NO_DONATION_IP_WHITELIST_FOUND') :
            trans('messages.success.MESSAGE_DONATION_IP_WHITELIST_LISTING');
        return $this->responseHelper->successWithPagination(
            Response::HTTP_OK,
            $message,
            $patterns
        );
    }

    /**
     * Store whitelisted IP
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'pattern' => 'required|max_item:donation_ip_whitelist,200|max:35|ip_whitelist_pattern|unique:donation_ip_whitelist,pattern,NULL,id,deleted_at,NULL',
                'description' => 'max:60'
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_DONATION_IP_WHITELIST_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        $whitelistIp = new DonationIpWhitelist();
        $whitelistIp->setAttribute('pattern', $request->get('pattern'))
            ->setAttribute('description', $request->get('description', null));

        $pattern = $this->whitelistService->create($whitelistIp);

        $this->logActions([
            'id' => $pattern->id,
            'actions' => config('constants.activity_log_actions.CREATED'),
            'request' => $request->all()
        ]);
        return $this->responseHelper->success(
            Response::HTTP_CREATED,
            trans('messages.success.MESSAGE_DONATION_IP_WHITELIST_CREATED'),
            [
                'id' => $pattern->id
            ]
        );
    }

    /**
     * Update whitelisted IP by ID
     *
     * @param Illuminate\Http\Request $request
     * @param string $id
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $whitelistedIp = $this->whitelistService->findById($id);
            $validator = Validator::make(
                $request->all(),
                [
                    'pattern' => "sometimes|required|max:35|ip_whitelist_pattern|unique:donation_ip_whitelist,pattern,$id,id,deleted_at,NULL",
                    'description' => 'max:60'
                ]
            );

            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_DONATION_IP_WHITELIST_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }

            $whitelistedIp->setAttribute('pattern', $request->get('pattern', null));
            if ($request->has('description')) {
                $whitelistedIp->setAttribute('description', $request->get('description'));
            }

            $updated = $this->whitelistService->update($whitelistedIp);

            $this->logActions([
                'id' => $id,
                'actions' => config('constants.activity_log_actions.UPDATED'),
                'request' => $request->all()
            ]);
            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_DONATION_IP_WHITELIST_UPDATED'),
                [
                    'id' => $id
                ]
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_DONATION_IP_WHITELIST_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_DONATION_IP_WHITELIST_NOT_FOUND')
            );
        }
    }

    /**
     * Delete whitelisted IP by ID
     *
     * @param string $id
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function delete($id): JsonResponse
    {
        try {
            $whitelisted = $this->whitelistService->findById($id);

            $deleted = $this->whitelistService->delete($id);
            $this->logActions([
                'id' => $whitelisted->id,
                'actions' => config('constants.activity_log_actions.DELETED')
            ]);
            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_DONATION_IP_WHITELIST_DELETED')
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_DONATION_IP_WHITELIST_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_DONATION_IP_WHITELIST_NOT_FOUND')
            );
        }
    }

    /**
     * Add event user logs
     *
     * @param array $payload
     *              $payload['actions']
     *              $payload['request']
     *              $payload['id']
     *
     * @return Illuminate\Http\JsonResponse
     */
    private function logActions($payload)
    {
        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.DONATION_IP_WHITELIST'),
            $payload['actions'] ?? null,
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $payload['request'] ?? [],
            null,
            $payload['id'] ?? null
        ));
    }

}
