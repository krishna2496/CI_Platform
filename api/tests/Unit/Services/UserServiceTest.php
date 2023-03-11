<?php

namespace Tests\Unit\Services;

use App\Exceptions\MaximumUsersReachedException;
use App\Models\MissionApplication;
use App\Models\ActivityLog;
use App\Models\TenantOption;
use App\Models\Timesheet;
use App\Models\FavouriteMission;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\User\UserRepository;
use App\Services\TenantOptionService;
use App\Services\UserService;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Mockery;
use TestCase;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Models\UserCustomFieldValue;
use Validator;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

/**
 * @coversDefaultClass  \App\Services\UserService
 */
class UserServiceTest extends TestCase
{
    /**
    * @testdox Test findById
    *
    * @return void
    */
    public function testFindById()
    {
        $user = new User();
        $user->setAttribute('user_id', 1);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('find')
            ->once()
            ->with($user->user_id)
            ->andReturn($user);

        $tenantOptionService = $this->mock(TenantOptionService::class);

        $service = $this->getService(
            $userRepository,
            $tenantOptionService
        );

        $response = $service->findById($user->user_id);

        $this->assertEquals($user, $response);
    }

    /**
    * @testdox Test statistics
    *
    * @return void
    */
    public function testStatistics()
    {
        $request = new Request();
        $methodResponse = $this->getMockResponse();
        $methodResponseTwo = $this->getMockResponseTwo();

        $user = new User();
        $user->setAttribute('user_id', 1);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('getStatistics')
            ->once()
            ->with($user, $request->all())
            ->andReturn($methodResponse);

        $userRepository
            ->shouldReceive('getOrgCount')
            ->once()
            ->with($user, $request->all())
            ->andReturn($methodResponseTwo);

        $tenantOptionService = $this->mock(TenantOptionService::class);

        $service = $this->getService(
            $userRepository,
            $tenantOptionService
        );

        $response = $service->statistics($user, $request->all());

        $this->assertEquals([
            'messages_count' => 5,
            'comments_count' => 3,
            'stories_count' => 2,
            'stories_views_count' => 3,
            'stories_invited_users_count' => 1,
            'organization_count' => 2
        ], $response);
    }

    /**
    * @testdox Test volunteer summary
    *
    * @return void
    */
    public function testVolunteersummary()
    {
        $request = new Request();

        $activityLog = new ActivityLog();
        $activityLog->setAttribute('last_login', '2020-05-15 10:10:31');
        $activityLog->setAttribute('last_volunteer', '2020-05-01');
        $activityLog = new Collection([$activityLog]);

        $missionCount = new User();
        $missionCount->setAttribute('open_volunteer_request', 1);
        $missionCount->setAttribute('mission', 1);
        $missionCount = new Collection([$missionCount]);

        $favoriteMission = new FavouriteMission();
        $favoriteMission->setAttribute('favourite_mission', 1);
        $favoriteMission = new Collection([$favoriteMission]);

        $user = new User();
        $user->setAttribute('user_id', 1);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('volunteerSummary')
            ->once()
            ->with($user, $request->all())
            ->andReturn($activityLog);

        $userRepository
            ->shouldReceive('getMissionCount')
            ->once()
            ->with($user, $request->all())
            ->andReturn($missionCount);

        $userRepository
            ->shouldReceive('getFavoriteMission')
            ->once()
            ->with($user, $request->all())
            ->andReturn($favoriteMission);

        $tenantOptionService = $this->mock(TenantOptionService::class);

        $service = $this->getService(
            $userRepository,
            $tenantOptionService
        );
        $response = $service->volunteerSummary($user, $request->all());
        $this->assertEquals([
            'last_volunteer' => '2020-05-01',
            'last_login' => '2020-05-15 10:10:31',
            'open_volunteer_request' => 1,
            'mission' => 1,
            'favourite_mission' => 1
        ], $response);
    }

    /**
     * Data provider for ::testStore().
     *
     * @return  array<string, array<int, mixed>>
     */
    public function storeData(): array
    {
        $_this = $this;
        $request = [];
        $user = new User();
        $getTenantOption = function ($value) {
            $tenantOption = new TenantOption();
            $tenantOption->option_name = TenantOption::MAXIMUM_USERS;
            $tenantOption->option_value = $value;
            return $tenantOption;
        };

        return [
            'No maximum users set' => [
                function () use ($_this, $request, $user) {
                    $userRepository = $_this->mock(UserRepository::class);
                    $userRepository->shouldReceive('store')
                        ->once()
                        ->with($request)
                        ->andReturn($user);

                    $tenantOptionService = $_this->mock(TenantOptionService::class);
                    $tenantOptionService->shouldReceive('getOptionValueFromOptionName')
                        ->once()
                        ->with(TenantOption::MAXIMUM_USERS)
                        ->andReturn(null);

                    return $_this->getService($userRepository, $tenantOptionService);
                },
                $request,
                null,
            ],
            'Unlimited users set' => [
                function () use ($_this, $request, $user, $getTenantOption) {
                    $userRepository = $_this->mock(UserRepository::class);
                    $userRepository->shouldNotReceive('getUserCount');
                    $userRepository->shouldReceive('store')
                        ->once()
                        ->with($request)
                        ->andReturn($user);

                    $tenantOption = $getTenantOption('-1');

                    $tenantOptionService = $_this->mock(TenantOptionService::class);
                    $tenantOptionService->shouldReceive('getOptionValueFromOptionName')
                        ->once()
                        ->with(TenantOption::MAXIMUM_USERS)
                        ->andReturn($tenantOption);

                    return $_this->getService($userRepository, $tenantOptionService);
                },
                $request,
                null,
            ],
            'Limited users set; limit not yet reached' => [
                function () use ($_this, $request, $user, $getTenantOption) {
                    $userRepository = $_this->mock(UserRepository::class);
                    $userRepository->shouldReceive('getUserCount')
                        ->once()
                        ->with(true, false)
                        ->andReturn(0);
                    $userRepository->shouldReceive('store')
                        ->once()
                        ->with($request)
                        ->andReturn($user);

                    $tenantOption = $getTenantOption('1');

                    $tenantOptionService = $_this->mock(TenantOptionService::class);
                    $tenantOptionService->shouldReceive('getOptionValueFromOptionName')
                        ->once()
                        ->with(TenantOption::MAXIMUM_USERS)
                        ->andReturn($tenantOption);

                    return $_this->getService($userRepository, $tenantOptionService);
                },
                $request,
                null,
            ],
            'Limited users set, limit already reached' => [
                function () use ($_this, $getTenantOption) {
                    $userRepository = $_this->mock(UserRepository::class);
                    $userRepository->shouldReceive('getUserCount')
                        ->once()
                        ->with(true, false)
                        ->andReturn(1);
                    $userRepository->shouldNotReceive('store');

                    $tenantOption = $getTenantOption('1');

                    $tenantOptionService = $_this->mock(TenantOptionService::class);
                    $tenantOptionService->shouldReceive('getOptionValueFromOptionName')
                        ->once()
                        ->with(TenantOption::MAXIMUM_USERS)
                        ->andReturn($tenantOption);

                    return $_this->getService($userRepository, $tenantOptionService);
                },
                $request,
                MaximumUsersReachedException::class,
            ],
            'Limited users set, limit already exceeded' => [
                function () use ($_this, $getTenantOption) {
                    $userRepository = $_this->mock(UserRepository::class);
                    $userRepository->shouldReceive('getUserCount')
                        ->once()
                        ->with(true, false)
                        ->andReturn(2);
                    $userRepository->shouldNotReceive('store');

                    $tenantOption = $getTenantOption('1');

                    $tenantOptionService = $_this->mock(TenantOptionService::class);
                    $tenantOptionService->shouldReceive('getOptionValueFromOptionName')
                        ->once()
                        ->with(TenantOption::MAXIMUM_USERS)
                        ->andReturn($tenantOption);

                    return $_this->getService($userRepository, $tenantOptionService);
                },
                $request,
                MaximumUsersReachedException::class,
            ],
        ];
    }

    /**
     * @param  callable
     * @param  array
     * @param  string|null
     *
     * @covers  ::store
     *
     * @dataProvider  storeData
     */
    public function testStore(callable $getUserService, array $request, ?string $expectedException = null): void
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $userService = $getUserService();
        $userService->store($request);
    }

    /**
     * @covers  ::update
     */
    public function testUpdate(): void
    {
        $request = [];
        $id = 1;

        $user = new User();
        $user->setAttribute('id', $id);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository->shouldReceive('update')
            ->once()
            ->with($request, $id)
            ->andReturn($user);

        $tenantOptionService = $this->mock(TenantOptionService::class);

        $userService = $this->getService($userRepository, $tenantOptionService);
        $res = $userService->update($request, $id);

        $this->assertSame($user, $res);
    }

    /**
     * @covers  ::getUserCount
     */
    public function testGetUserCount(): void
    {
        $activeStatus = [
            'inactive' => false,
            'active' => true,
        ];
        $userCount = [
            'inactive' => 3,
            'active' => 9,
        ];

        $userRepository = $this->mock(UserRepository::class);
        $_this = $this;
        $userRepository->shouldReceive('getUserCount')
            ->andReturnUsing(function ($active) use ($_this, $activeStatus, $userCount) {
                $_this->assertIsBool($active);
                $status = array_search($active, $activeStatus);
                return $userCount[$status];
            });

        $tenantOptionService = $this->mock(TenantOptionService::class);

        $userService = $this->getService($userRepository, $tenantOptionService);

        $res = $userService->getUserCount(false);

        $this->assertSame($userCount['inactive'], $res);

        $res = $userService->getUserCount(true);

        $this->assertSame($userCount['active'], $res);
    }

    /**
     * Data provider for ::testStoreWithCustomFields().
     *
     * @return  array<string, array<int, mixed>>
     */
    public function storeWithCustomFieldsData(): array
    {
        $_this = $this;
        $request = [
            'custom_fields' => [
                [
                    'field_id' => 1,
                    'value' => 'First Name'
                ]
            ]
        ];
        $user = new User();
        $user->setAttribute('user_id', 1);
        $userCustomFieldValue = new UserCustomFieldValue();
        $getTenantOption = function ($value) {
            $tenantOption = new TenantOption();
            $tenantOption->option_name = TenantOption::MAXIMUM_USERS;
            $tenantOption->option_value = $value;
            return $tenantOption;
        };

        return [
            [
                function () use ($_this, $request, $user, $userCustomFieldValue) {
                    $userRepository = $_this->mock(UserRepository::class);
                    $userRepository->shouldReceive('store')
                        ->once()
                        ->with($request)
                        ->andReturn($user);

                    $userRepository->shouldReceive('updateCustomFields')
                        ->once()
                        ->with($request['custom_fields'], 1)
                        ->andReturn($userCustomFieldValue);

                    $tenantOptionService = $_this->mock(TenantOptionService::class);
                    $tenantOptionService->shouldReceive('getOptionValueFromOptionName')
                        ->once()
                        ->with(TenantOption::MAXIMUM_USERS)
                        ->andReturn(null);

                    return $_this->getService($userRepository, $tenantOptionService);
                },
                $request
            ]
        ];
    }

    /**
     * @param  callable
     * @param  array
     * @param  string|null
     *
     * @covers  ::store
     *
     * @dataProvider  storeWithCustomFieldsData
     */
    public function testStoreWithCustomFields(callable $getUserService, array $request): void
    {
        $userService = $getUserService();
        $userService->store($request);
    }

    /**
     * @covers  ::update
     */
    public function testUpdateWithCustomFields(): void
    {
        $request = [
            'custom_fields' => [
                [
                    'field_id' => 1,
                    'value' => 'First Name'
                ]
            ]
        ];
        $id = 1;

        $user = new User();
        $user->setAttribute('id', $id);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository->shouldReceive('update')
            ->once()
            ->with($request, $id)
            ->andReturn($user);

        $userRepository
            ->shouldReceive('updateCustomFields')
            ->once()
            ->with($request['custom_fields'], 1)
            ->andReturn(new UserCustomFieldValue);

        $tenantOptionService = $this->mock(TenantOptionService::class);

        $userService = $this->getService($userRepository, $tenantOptionService);
        $res = $userService->update($request, $id);

        $this->assertSame($user, $res);
    }

    /**
    * @testdox Test link skill
    */
    public function testLinkSkill()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $data = [
            'skills' => [
                ['skill_id' => 3]
            ]
        ];

        $userRepository
            ->shouldReceive('linkSkill')
            ->once()
            ->with($data, 1)
            ->andReturn([['skill_id' => 1]]);

        $service = $this->getService(
            $userRepository,
            $tenantOptionService
        );
        $response = $service->linkSkill($data, 1);
        $this->assertEquals([
            ['skill_id' => 1]
        ], $response);
    }

    /**
    * @testdox Test update skill
    */
    public function testUpdateSkill()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $data = [
            'skills' => [
                ['skill_id' => 3],
                ['skill_id' => 4]
            ]
        ];

        $userRepository
            ->shouldReceive('linkSkill')
            ->once()
            ->with($data, 1)
            ->andReturn([['skill_id' => 1], ['skill_id' => 2]]);

        $userRepository
            ->shouldReceive('deleteSkills')
            ->once()
            ->with(1)
            ->andReturn(true);

        $service = $this->getService(
            $userRepository,
            $tenantOptionService
        );
        $response = $service->updateSkill($data, 1);
        $this->assertEquals([
            ['skill_id' => 1],
            ['skill_id' => 2]
        ], $response);
    }

    /**
    * @testdox Test update custom fields value
    */
    public function testUpdateCustomFieldsValue()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $data = [
            'custom_fields' => [
                [
                    'field_id' => 1,
                    'value' => 'First Name'
                ]
            ]
        ];

        $userCustomFieldValue = new UserCustomFieldValue();
        $userCustomFieldValue->setAttribute('field_id', 1);
        $userCustomFieldValue->setAttribute('user_id', 1);
        $userCustomFieldValue->setAttribute('value', 'First Name');

        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('updateCustomFields')
            ->once()
            ->with($data['custom_fields'], 1)
            ->andReturn($userCustomFieldValue);

        $service = $this->getService(
            $userRepository,
            $tenantOptionService
        );
        $response = $service->updateCustomFieldsValue($data['custom_fields'], 1);
        $this->assertEquals($userCustomFieldValue, $response);
    }

    /**
    * @testdox Test validate fields for create method
    */
    public function testValidateFieldsCreateMethodAPISuccess()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $requestData = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'firstlast@email.com',
            'password' => 'Qwerty1234',
            'skills' => [['skill_id' => 1]],
            'custom_fields' => [['field_id' => 1, 'value' => 'test']]
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator
            ->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $service = $this->getService($userRepository, $tenantOptionService);
        $response = $service->validateFields($requestData);
        $this->assertEquals(true, $response);
    }

    /**
    * @testdox Test validate fields for create method with invalid data
    */
    public function testValidateFieldsCreateMethodAPIInvalid()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $requestData = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'firstlast@email.com',
            'skills' => [['skill_id' => 1]],
            'custom_fields' => [['field_id' => 1, 'value' => 'test']]
        ];

        $errors = new Collection([
            'Password field is required'
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator
            ->shouldReceive('fails')
            ->once()
            ->andReturn(true);

        $validator
            ->shouldReceive('errors')
            ->once()
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                $errors->first()
            );

        $service = $this->getService(
            $userRepository,
            $tenantOptionService,
            null,
            $responseHelper
        );
        $response = $service->validateFields($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test validate fields for update method
    */
    public function testValidateFieldsUpdateMethodAPISuccess()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $requestData = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'firstlast@email.com',
            'password' => 'Qwerty1234',
            'skills' => [['skill_id' => 1]],
            'custom_fields' => [['field_id' => 1, 'value' => 'test']]
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator
            ->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $service = $this->getService($userRepository, $tenantOptionService);
        $response = $service->validateFields($requestData, 1);
        $this->assertEquals(true, $response);
    }

    /**
    * @testdox Test validate fields for update method with pseudonymize at field
    */
    public function testValidateFieldsUpdateMethodAPISuccessWithPseudonymizeAt()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $requestData = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'firstlast@email.com',
            'password' => 'Qwerty1234',
            'skills' => [['skill_id' => 1]],
            'custom_fields' => [['field_id' => 1, 'value' => 'test']],
            'pseudonymize_at' => '2020-10-02 12:32:29.0',
            'employee_id' => ''
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator
            ->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $helpers = $this->mock(Helpers::class);
        $helpers
            ->shouldReceive('getSupportedFieldsToPseudonymize')
            ->once()
            ->andReturn([
                'first_name',
                'last_name',
                'email',
                'employee_id',
                'linked_in_url',
                'position',
                'department',
                'profile_text',
                'why_i_volunteer'
            ]);

        $user = new User();
        $user->setAttribute('user_id', 1);
        $userRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($user);

        $service = $this->getService($userRepository, $tenantOptionService, $helpers);
        $response = $service->validateFields($requestData, 1);
        $this->assertEquals(true, $response);
    }

    /**
    * @testdox Test validate fields for create method with invalid data
    */
    public function testValidateFieldsUpdateMethodAPIInvalid()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $requestData = [
            'last_name' => 'Last',
            'password' => 'Qwerty1234',
            'skills' => [['skill_id' => 1]],
            'custom_fields' => [['field_id' => 1, 'value' => 'test']]
        ];

        $errors = new Collection([
            'Email field is required'
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator
            ->shouldReceive('fails')
            ->once()
            ->andReturn(true);

        $validator
            ->shouldReceive('errors')
            ->once()
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                $errors->first()
            );

        $service = $this->getService(
            $userRepository,
            $tenantOptionService,
            null,
            $responseHelper
        );
        $response = $service->validateFields($requestData, 1);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test validate fields for update method
    */
    public function testValidateFieldsUpdateMethodAppSuccess()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $requestData = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'firstlast@email.com',
            'password' => 'Qwerty1234',
            'skills' => [['skill_id' => 1]],
            'custom_fields' => [['field_id' => 1, 'value' => 'test']]
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator
            ->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $service = $this->getService($userRepository, $tenantOptionService);
        $response = $service->validateFields($requestData, 1, false);
        $this->assertEquals(true, $response);
    }

    /**
    * @testdox Test validate fields for update method with invalid data
    */
    public function testValidateFieldsUpdateMethodAppInvalid()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $requestData = [
            'last_name' => 'Last',
            'email' => 'firstlast@email.com',
            'password' => 'Qwerty1234',
            'skills' => [['skill_id' => 1]],
            'custom_fields' => [['field_id' => 1, 'value' => 'test']]
        ];

        $errors = new Collection([
            'First name field is required'
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator
            ->shouldReceive('fails')
            ->once()
            ->andReturn(true);

        $validator
            ->shouldReceive('errors')
            ->once()
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                $errors->first()
            );

        $service = $this->getService(
            $userRepository,
            $tenantOptionService,
            null,
            $responseHelper
        );
        $response = $service->validateFields($requestData, 1, false);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test unset pseudonymized fields
    */
    public function testUnsetPseudonymizedFields()
    {
        $tenantOptionService = $this->mock(TenantOptionService::class);
        $userRepository = $this->mock(UserRepository::class);
        $helpers = $this->mock(Helpers::class);
        $helpers
            ->shouldReceive('getSupportedFieldsToPseudonymize')
            ->once()
            ->andReturn([
                'first_name',
                'last_name',
                'email',
                'employee_id',
                'linked_in_url',
                'position',
                'department',
                'profile_text',
                'why_i_volunteer'
            ]);

        $requestData = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'firstlast@email.com',
            'password' => 'Qwerty1234',
            'availability_id' => 1,
            'timezone_id' => 1,
            'language_id' => 1,
            'city_id' => 1,
            'country_id' => 1,
            'profile_text' => 'text profile',
            'department' => 'department',
            'skills' => [['skill_id' => 1]],
            'custom_fields' => [['field_id' => 1, 'value' => 'test']],
            'pseudonymize_at' => '2020-10-02 12:32:29.0',
            'employee_id' => 1
        ];

        $service = $this->getService(
            $userRepository,
            $tenantOptionService,
            $helpers
        );
        $response = $service->unsetPseudonymizedFields($requestData);
        $this->assertCount(8, $response);
    }

    private function getMockResponse()
    {
        $user = new User();
        $user->setAttribute('messages_count', 5);
        $user->setAttribute('comments_count', 3);
        $user->setAttribute('stories_count', 2);
        $user->setAttribute('stories_views_count', 3);
        $user->setAttribute('stories_invited_users_count', 1);

        return new Collection([
            $user
        ]);
    }

    private function getMockResponseTwo()
    {
        $mission = new MissionApplication();
        $mission->setAttribute('organization_count', 2);

        return new Collection([
            $mission
        ]);
    }

    /**
     * Create a new service instance.
     *
     * @param  UserRepository       $userRepository
     * @param  TenantOptionService  $tenantOptionService
     *
     * @return void
     */
    private function getService(
        UserRepository $userRepository,
        TenantOptionService $tenantOptionService,
        Helpers $helpers = null,
        ResponseHelper $responseHelper = null
    ) {
        $responseHelper = $responseHelper ?? $this->mock(ResponseHelper::class);
        $helpers = $helpers ?? $this->mock(Helpers::class);

        return new UserService(
            $userRepository,
            $tenantOptionService,
            $helpers,
            $responseHelper
        );
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
