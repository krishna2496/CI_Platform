<?php

namespace Tests\Unit\Http\Controllers\Admin\User;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\User\UserCustomFieldController;
use App\Models\UserCustomField;
use App\Repositories\UserCustomField\UserCustomFieldRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use TestCase;
use Validator;

class UserCustomFieldControllerTest extends TestCase
{
    /**
     * Test index with success status
     *
     * @return void
     */
    public function testIndexSuccess()
    {
        $request = new Request();
        $customFields = $this->customFields();

        $paginator = $this->getPaginator($customFields, count($customFields), 2);
        $repository = $this->mock(UserCustomFieldRepository::class);
        $repository->shouldReceive('userCustomFieldList')
            ->once()
            ->with($request)
            ->andReturn($paginator);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('successWithPagination')
            ->once()
            ->with(Response::HTTP_OK, trans('messages.success.MESSAGE_CUSTOM_FIELD_LISTING'), $paginator);

        $response = $this->getController($repository, $responseHelper, $request)
            ->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test index with no records found
     *
     * @return void
     */
    public function testIndexNoRecords()
    {
        $request = new Request();
        $customFields = [];

        $paginator = $this->getPaginator($customFields, count($customFields), 2);
        $repository = $this->mock(UserCustomFieldRepository::class);
        $repository->shouldReceive('userCustomFieldList')
            ->once()
            ->with($request)
            ->andReturn($paginator);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('successWithPagination')
            ->once()
            ->with(Response::HTTP_OK, trans('messages.success.MESSAGE_NO_RECORD_FOUND'), $paginator);

        $response = $this->getController($repository, $responseHelper, $request)
            ->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test store() method
     *
     * @return void
     */
    public function testStore()
    {
        $dbRecord = ['field_id' => 1];

        $userCustomFieldModel = $this->mock(UserCustomField::class);
        $userCustomFieldModel->shouldReceive('offsetGet')
            ->andReturn($dbRecord['field_id']);

        $repository = $this->mock(UserCustomFieldRepository::class);
        $repository->shouldReceive('store')
            ->andReturn($userCustomFieldModel)
            ->shouldReceive('findMaxOrder');

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success')
            ->once()
            ->with(Response::HTTP_CREATED, trans('messages.success.MESSAGE_CUSTOM_FIELD_ADDED'), $dbRecord);

        $request = $this->mock(Request::class);
        $request->shouldReceive('header')
            ->shouldReceive('toArray')
            ->andReturn([
                'name' => 'custom-textarea',
                'type' => 'textarea',
                'is_mandatory' => 'true',
                'translations' => [
                    ['lang' => 'en', 'name' => 'Custom Textarea']
                ],
                'internal_note' => 'note'
            ])
            ->shouldReceive('all')
            ->shouldReceive('route')
            ->shouldReceive('merge');

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->withoutEvents();

        $response = $this->getController($repository, $responseHelper, $request)
            ->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update() method
     *
     * @return void
     */
    public function testUpdate()
    {
        $dbRecord = ['field_id' => 1];

        $userCustomFieldModel = $this->mock(UserCustomField::class);
        $userCustomFieldModel->shouldReceive('offsetGet')
            ->andReturn($dbRecord['field_id'])
            ->shouldReceive('getAttribute');

        $repository = $this->mock(UserCustomFieldRepository::class);
        $repository->shouldReceive('update')
            ->andReturn($userCustomFieldModel)
            ->shouldReceive('find')
            ->andReturn($userCustomFieldModel);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success')
            ->once()
            ->with(Response::HTTP_OK, trans('messages.success.MESSAGE_CUSTOM_FIELD_UPDATED'), $dbRecord);

        $request = $this->mock(Request::class);
        $request->shouldReceive('header')
            ->shouldReceive('toArray')
            ->andReturn([
                'name' => 'custom-textarea',
                'type' => 'textarea',
                'is_mandatory' => 'true',
                'translations' => [
                    ['lang' => 'en', 'name' => 'Custom Textarea']
                ],
                'internal_note' => 'note'
            ])
            ->shouldReceive('all')
            ->shouldReceive('route');

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->withoutEvents();

        $response = $this->getController($repository, $responseHelper, $request)
            ->update($request, $dbRecord['field_id']);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test show() method
     *
     * @return void
     */
    public function testShow()
    {
        $dbRecord = ['field_id' => 1];

        $userCustomFieldModel = $this->mock(UserCustomField::class);
        $userCustomFieldModel->shouldReceive('toArray')
            ->andReturn($dbRecord);

        $repository = $this->mock(UserCustomFieldRepository::class);
        $repository->shouldReceive('find')
            ->andReturn($userCustomFieldModel);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success')
            ->once()
            ->with(Response::HTTP_OK, trans('messages.success.MESSAGE_CUSTOM_FIELD_FOUND'), $dbRecord);

        $request = $this->mock(Request::class);
        $request->shouldReceive('header');

        $response = $this->getController($repository, $responseHelper, $request)
            ->show($dbRecord['field_id']);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test destroy() method
     *
     * @return void
     */
    public function testDestroy()
    {
        $dbRecord = ['field_id' => 1];

        $repository = $this->mock(UserCustomFieldRepository::class);
        $repository->shouldReceive('delete')
            ->andReturn(true)
            ->shouldReceive('deleteMultiple')
            ->shouldReceive('findMinOrder');

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success')
            ->once()
            ->with(Response::HTTP_NO_CONTENT, trans('messages.success.MESSAGE_CUSTOM_FIELD_DELETED'));

        $responseHelper->shouldReceive('error')
            ->never()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_INVALID_DATA'),
                'Field id must be an array'
            );

        $request = $this->mock(Request::class);
        $request->shouldReceive('header')
            ->shouldReceive('toArray')
            ->andReturn(['id' => [2]]);
        $request->shouldReceive('has')
            ->shouldReceive('all')
            ->andReturn([]);

        $this->withoutEvents();

        $response = $this->getController($repository, $responseHelper, $request)
            ->destroy($request, $dbRecord['field_id']);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Returns sample of custom fields
     *
     * @return array
     */
    private function customFields()
    {
        return [
            [
                'field_id' => 1,
                'name' => 'Custom Checkbox',
                'type' => 'checkbox',
                'is_mandatory' => 1,
                'internal_note' => ''
            ],
            [
                'field_id' => 2,
                'name' => 'Custom Multi Select',
                'type' => 'multiselect',
                'is_mandatory' => 1,
                'internal_note' => ''
            ],
            [
                'field_id' => 3,
                'name' => 'Custom Textarea',
                'type' => 'textarea',
                'is_mandatory' => 1,
                'internal_note' => ''
            ]
        ];
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
     * Mocks an object
     *
     * @param string $class
     *
     * @return Mockery
     */
    private function mock($class)
    {
        return Mockery::mock($class);
    }

    /**
     * Creates an instance of UserCustomFieldController
     *
     * @param UserCustomFieldRepository $userCustomFieldRepository
     * @param ResponseHelper $responseHelper
     * @param Request $request
     *
     * @return UserCustomFieldController
     */
    private function getController(
        UserCustomFieldRepository $userCustomFieldRepository,
        ResponseHelper $responseHelper,
        Request $request
    ) {
        return new UserCustomFieldController(
            $userCustomFieldRepository,
            $responseHelper,
            $request
        );
    }
}
