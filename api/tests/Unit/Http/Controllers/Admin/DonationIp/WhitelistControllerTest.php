<?php

namespace Tests\Unit\Http\Controllers\Admin\DoantionIp;

use App\Events\User\UserActivityLogEvent;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\DonationIp\WhitelistController;
use App\Models\DonationIpWhitelist;
use App\Services\DonationIp\WhitelistService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use TestCase;
use Validator;

class WhitelistControllerTest extends TestCase
{
    /**
     * Test getList with success status
     *
     * @return void
     */
    public function testGetList()
    {
        $paginate = [
            'perPage' => 10
        ];
        $filters = [
            'search' => 'sample',
            'order' => [
                'orderBy' => 'pattern',
                'orderDir' => 'asc'
            ]
        ];
        $request = new Request();
        $request->query->add(array_merge($paginate, $filters));

        $whitelistedIp = $this->whitelistedIp();
        $paginator = $this->getPaginator(
            $whitelistedIp,
            count($whitelistedIp),
            $paginate['perPage']
        );

        $filters['order'] = [
            $filters['order']['orderBy'] => $filters['order']['orderDir']
        ];

        $whitelistService = $this->mock(WhitelistService::class);
        $whitelistService->shouldReceive('getList')
            ->once()
            ->with($paginate, $filters)
            ->andReturn($paginator);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('successWithPagination')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_DONATION_IP_WHITELIST_LISTING'),
                $paginator
            );

        $response = $this->getController(
            $whitelistService,
            $responseHelper,
            $request
        )->getList($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test getList with success status
     *
     * @return void
     */
    public function testGetListNoResult()
    {
        $paginate = [
            'perPage' => 10
        ];
        $filters = [
            'search' => 'sample',
            'order' => [
                'orderBy' => 'pattern',
                'orderDir' => 'asc'
            ]
        ];
        $request = new Request();
        $request->query->add(array_merge($paginate, $filters));
        $paginator = $this->getPaginator(
            [],
            0,
            $paginate['perPage']
        );

        $filters['order'] = [
            $filters['order']['orderBy'] => $filters['order']['orderDir']
        ];

        $whitelistService = $this->mock(WhitelistService::class);
        $whitelistService->shouldReceive('getList')
            ->once()
            ->with($paginate, $filters)
            ->andReturn($paginator);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('successWithPagination')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_NO_DONATION_IP_WHITELIST_FOUND'),
                $paginator
            );

        $response = $this->getController(
            $whitelistService,
            $responseHelper,
            $request
        )->getList($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test create method
     *
     * @return void
     */
    public function testCreate()
    {
        $this->expectsEvents(UserActivityLogEvent::class);

        $whitelistIp = new DonationIpWhitelist();
        $whitelistIp->setAttribute('pattern', '192.168.0.0/26')
            ->setAttribute('description', 'IDR IP');

        $request = new Request();
        $request->query->add($whitelistIp->toArray());

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $whitelisted = (Object) [
            'id' => '22fc6b4e-a1b6-4e47-83a2-0dda0785f1c1'
        ];

        $whitelistService = $this->mock(WhitelistService::class);
        $whitelistService->shouldReceive('create')
            ->once()
            ->andReturn($whitelisted);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_CREATED,
                trans('messages.success.MESSAGE_DONATION_IP_WHITELIST_CREATED'),
                [
                    'id' => $whitelisted->id
                ]
            );

        $response = $this->getController(
            $whitelistService,
            $responseHelper,
            $request
        )->create($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test create method with expected validation error
     *
     * @return void
     */
    public function testCreateValidation()
    {
        $request = new Request();
        $errors = new Collection([
            'sample-error message'
        ]);
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_DONATION_IP_WHITELIST_INVALID_DATA'),
                $errors->first()
            );

        $whitelistService = $this->mock(WhitelistService::class);
        $whitelistService->shouldReceive('create')
            ->never();

        $response = $this->getController(
            $whitelistService,
            $responseHelper,
            $request
        )->create($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->expectsEvents(UserActivityLogEvent::class);

        $whitelisted = $this->whitelistedIp()->first();
        $request = new Request();
        $request->query->add([
            'pattern' => $whitelisted->pattern,
            'description' => $whitelisted->description
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $whitelistService = $this->mock(WhitelistService::class);
        $whitelistService->shouldReceive('findById')
            ->once()
            ->with($whitelisted->id)
            ->andReturn($whitelisted)
            ->shouldReceive('update')
            ->once()
            ->with($whitelisted)
            ->andReturn($whitelisted);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_DONATION_IP_WHITELIST_UPDATED'),
                [
                    'id' => $whitelisted->id
                ]
            );

        $response = $this->getController(
            $whitelistService,
            $responseHelper,
            $request
        )->update($request, $whitelisted->id);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method with expected validation error
     *
     * @return void
     */
    public function testUpdateValidation()
    {
        $request = new Request();

        $whitelisted =  $this->whitelistedIp()->first();
        $whitelistService = $this->mock(WhitelistService::class);
        $whitelistService->shouldReceive('findById')
            ->once()
            ->with($whitelisted->id)
            ->andReturn($whitelisted);

        $errors = new Collection([
            'sample-error message'
        ]);
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_DONATION_IP_WHITELIST_INVALID_DATA'),
                $errors->first()
            );

        $response = $this->getController(
            $whitelistService,
            $responseHelper,
            $request
        )->update($request, $whitelisted->id);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method with expected exception
     *
     * @return void
     */
    public function testUpdateException()
    {
        $request = new Request();

        $id = '22fc6b4e-a1b6-4e47-83a2-0dda0785f1c1';
        $whitelistService = $this->mock(WhitelistService::class);
        $whitelistService->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andThrow(new ModelNotFoundException);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_DONATION_IP_WHITELIST_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_DONATION_IP_WHITELIST_NOT_FOUND')
            );

        $response = $this->getController(
            $whitelistService,
            $responseHelper,
            $request
        )->update($request, $id);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->expectsEvents(UserActivityLogEvent::class);

        $request = new Request();
        $whitelisted = $this->whitelistedIp()->first();
        $whitelistService = $this->mock(WhitelistService::class);
        $whitelistService->shouldReceive('findById')
            ->once()
            ->with($whitelisted->id)
            ->andReturn($whitelisted)
            ->shouldReceive('delete')
            ->once()
            ->with($whitelisted->id)
            ->andReturn(true);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_DONATION_IP_WHITELIST_DELETED')
            );

        $response = $this->getController(
            $whitelistService,
            $responseHelper,
            $request
        )->delete($whitelisted->id);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test delete method with expected exception
     *
     * @return void
     */
    public function testDeleteException()
    {
        $id = '22fc6b4e-a1b6-4e47-83a2-0dda0785f1c1';
        $request = new Request();

        $whitelistService = $this->mock(WhitelistService::class);
        $whitelistService->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andThrow(new ModelNotFoundException)
            ->shouldReceive('delete')
            ->never();

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_DONATION_IP_WHITELIST_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_DONATION_IP_WHITELIST_NOT_FOUND')
            );

        $response = $this->getController(
            $whitelistService,
            $responseHelper,
            $request
        )->delete($id);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Returns sample of custom fields
     *
     * @return array
     */
    private function whitelistedIp()
    {
        return new Collection([
            factory(DonationIpWhitelist::class)->make(),
            factory(DonationIpWhitelist::class)->make()
        ]);
    }

    /**
     * Create a new controller instance.
     *
     * @param  WhitelistService $tenantActivatedSettingRepository
     * @param  ResponseHelper $responseHelper
     * @param  Request $helpers
     *
     * @return void
     */
    private function getController(
        WhitelistService $whitelistService,
        ResponseHelper $responseHelper,
        Request $request
    ) {
        return new WhitelistController(
            $whitelistService,
            $responseHelper,
            $request
        );
    }

    /**
     * Creates an instance of LengthAwarePaginator
     *
     * @param array $items
     * @param integer $total
     * @param integer $perPage
     *
     * @return LengthAwarePaginator
     */
    private function getPaginator($items, $total, $perPage)
    {
        return new LengthAwarePaginator($items, $total, $perPage);
    }

    /**
    * Mock an object
    *
    * @param string name
    *
    * @return Mockery
    */
    private function mock($class)
    {
        return Mockery::mock($class);
    }
}
