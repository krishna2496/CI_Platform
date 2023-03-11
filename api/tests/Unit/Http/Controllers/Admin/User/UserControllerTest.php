<?php

namespace Tests\Unit\Http\Controllers\App\User;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\User\UserController;
use App\Repositories\User\UserRepository;
use App\Services\TimesheetService;
use App\Services\UserService;
use App\Repositories\Notification\NotificationRepository;
use App\Repositories\Timezone\TimezoneRepository;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use TestCase;
use App\Models\Timezone;
use App\Events\User\UserActivityLogEvent;
use App\Exceptions\MaximumUsersReachedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class UserControllerTest extends TestCase
{

    /**
    * @testdox Test contentStatistics
    *
    * @return void
    */
    public function testContentStatistics()
    {
        $request = new Request();
        $methodResponse = [
            'messages_count' => 5,
            'comments_count' => 3,
            'stories_count' => 2,
            'stories_views_count' => 3,
            'stories_invites_count' => 1,
            'organization_count' => 2
        ];

        $user = new User();
        $user->setAttribute('user_id', 1);

        $userService = $this->mock(UserService::class);
        $userService
            ->shouldReceive('findById')
            ->once()
            ->with($user->user_id)
            ->andReturn($user);

        $userService
            ->shouldReceive('statistics')
            ->once()
            ->with($user, $request->all())
            ->andReturn($methodResponse);

        $jsonResponse = new JsonResponse(
            $methodResponse,
            Response::HTTP_OK
        );

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_TENANT_USER_CONTENT_STATISTICS_SUCCESS'),
                $methodResponse
            )
            ->andReturn($jsonResponse);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);

        $service = $this->getController(
            null,
            $responseHelper,
            null,
            $userService,
            null,
            null,
            $request,
            $notificationRepository,
            $timezoneRepository
        );

        $response = $service->contentStatistics($request, $user->user_id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals([
            'messages_count' => 5,
            'comments_count' => 3,
            'stories_count' => 2,
            'stories_views_count' => 3,
            'stories_invites_count' => 1,
            'organization_count' => 2
        ], json_decode($response->getContent(), true));
    }

    /**
    * @testdox Test volunteerSummary
    *
    * @return void
    */
    public function testVolunteerSummary()
    {
        $request = new Request();
        $methodResponse = [
            'last_volunteer' => '2020-05-01',
            'last_login' => '2020-05-15 10:10:31',
            'open_volunteer_request' => 1,
            'mission' => 1,
            'favourite_mission' => 1
        ];

        $user = new User();
        $user->setAttribute('user_id', 1);

        $userService = $this->mock(UserService::class);
        $userService
            ->shouldReceive('findById')
            ->once()
            ->with($user->user_id)
            ->andReturn($user);

        $userService
            ->shouldReceive('volunteerSummary')
            ->once()
            ->with($user, $request->all())
            ->andReturn($methodResponse);

        $jsonResponse = new JsonResponse(
            $methodResponse,
            Response::HTTP_OK
        );

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_TENANT_USER_VOLUNTEER_SUMMARY_SUCCESS'),
                $methodResponse
            )
            ->andReturn($jsonResponse);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);

        $service = $this->getController(
            null,
            $responseHelper,
            null,
            $userService,
            null,
            null,
            $request,
            $notificationRepository,
            $timezoneRepository
        );

        $response = $service->volunteerSummary($request, $user->user_id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals([
            'last_volunteer' => '2020-05-01',
            'last_login' => '2020-05-15 10:10:31',
            'open_volunteer_request' => 1,
            'mission' => 1,
            'favourite_mission' => 1
        ], json_decode($response->getContent(), true));
    }

    /**
    * @testdox Test store success scenario
    *
    * @return void
    */
    public function testStoreSuccess()
    {
        $mergeData = [
            'timezone_id' => 1,
            'expiry' => null,
            'pseudonymize_at' => null
        ];
        $exceptData = ['email' => 'testuser@gmail.com'];
        $data = [
            'email' => 'testuser@gmail.com',
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'status' => 1
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData);
        $request->language_id = 1;
        $request->timezone_id = null;
        $request->expiry = null;
        $request->skills = [['skill_id' => 1]];

        $userService = $this->mock(UserService::class);
        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all())
            ->andReturn(true);

        $userService
            ->shouldReceive('linkSkill')
            ->once()
            ->with($exceptData, 1);

        $user = new User();
        $user->setAttribute('user_id', 1);

        $userService
            ->shouldReceive('store')
            ->once()
            ->with($request->all())
            ->andReturn($user);

        $languageHelper = $this->mock(LanguageHelper::class);
        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);

        $timezone = new Timezone();
        $timezone->setAttribute('timezone_id', 1);

        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $timezoneRepository
            ->shouldReceive('getTenantTimezoneByCode')
            ->once()
            ->with('Europe/Paris')
            ->andReturn($timezone);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($user);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_CREATED,
                trans('messages.success.MESSAGE_USER_CREATED'),
                ['user_id' => 1]
            );
        $this->expectsEvents(UserActivityLogEvent::class);

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            $timezoneRepository
        );
        $response = $controller->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test store invalid validation scenario
    *
    * @return void
    */
    public function testStoreInvalidValidation()
    {
        $mergeData = [
            'timezone_id' => 1,
            'expiry' => null,
            'pseudonymize_at' => null
        ];
        $exceptData = ['email' => 'testuser@gmail.com'];
        $data = [
            'email' => 'testuser@gmail.com',
            'language_id' => 1
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data);

        $userService = $this->mock(UserService::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $userRepository = $this->mock(UserRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all())
            ->andReturn(new JsonResponse());

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            $timezoneRepository
        );
        $response = $controller->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test store invalid language ID scenario
    *
    * @return void
    */
    public function testStoreInvalidLanguageId()
    {
        $mergeData = [
            'timezone_id' => 1,
            'expiry' => null,
            'pseudonymize_at' => null
        ];
        $exceptData = ['email' => 'testuser@gmail.com'];
        $data = [
            'email' => 'testuser@gmail.com',
            'language_id' => 1
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data);

        $userService = $this->mock(UserService::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $userRepository = $this->mock(UserRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all())
            ->andReturn(true);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(false);

        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                trans('messages.custom_error_message.ERROR_USER_INVALID_LANGUAGE')
            );

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            $timezoneRepository
        );
        $response = $controller->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test store will throw exception scenario
    *
    * @return void
    */
    public function testStoreInvalidThrowException()
    {
        $mergeData = [
            'timezone_id' => 1,
            'expiry' => null,
            'pseudonymize_at' => null
        ];
        $exceptData = ['email' => 'testuser@gmail.com'];
        $data = [
            'email' => 'testuser@gmail.com',
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'timezone_id' => 1,
            'expiry' => null,
            'status' => 1
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData));

        $userService = $this->mock(UserService::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $userRepository = $this->mock(UserRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all())
            ->andReturn(true);

        $userService
            ->shouldReceive('store')
            ->once()
            ->with($request->all())
            ->andThrow(new MaximumUsersReachedException);
        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);
        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_BAD_REQUEST,
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                config('constants.error_codes.ERROR_MAXIMUM_USERS_REACHED'),
                trans('messages.custom_error_message.ERROR_MAXIMUM_USERS_REACHED')
            );

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            $timezoneRepository
        );
        $response = $controller->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test update success scenario
    *
    * @return void
    */
    public function testUpdateSuccess()
    {
        $mergeData = [
            'avatar' => null,
            'expiry' => null
        ];
        $exceptData = [
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null
        ];
        $data = [
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]],
            'status' => 1
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData);

        $userService = $this->mock(UserService::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $userRepository = $this->mock(UserRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all(), 1)
            ->andReturn(true);

        $userDetail = new User();
        $userDetail->setAttribute('pseudonymize_at', null);
        $userDetail->setAttribute('user_id', 1);

        $userService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($userDetail);

        $userService
            ->shouldReceive('update')
            ->once()
            ->with($request->all(), 1)
            ->andReturn($userDetail);

        $userService
            ->shouldReceive('updateSkill')
            ->once()
            ->andReturn([['skill_id' => 1]]);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);

        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($userDetail);

        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_UPDATED'),
                ['user_id' => 1]
            );

        $this->expectsEvents(UserActivityLogEvent::class);

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            $timezoneRepository
        );
        $response = $controller->update($request, 1);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test update success with pseudonymized user data scenario
    *
    * @return void
    */
    public function testUpdateSuccessWithPseudonymizeUserData()
    {
        $mergeData = [
            'avatar' => null,
            'expiry' => null
        ];
        $exceptData = [
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null
        ];
        $data = [
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]],
            'department' => 'The Department',
            'employee_id' => 123,
            'status' => 1
        ];
        $notPseudonymizeFields = [
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]],
            'status' => 1
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData);

        $userService = $this->mock(UserService::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $userRepository = $this->mock(UserRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all(), 1)
            ->andReturn(true);

        $userDetail = new User();
        $userDetail->setAttribute('pseudonymize_at', '2020-10-02 12:32:29.0');
        $userDetail->setAttribute('user_id', 1);

        $userService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($userDetail);

        $userService
            ->shouldReceive('update')
            ->once()
            ->with($notPseudonymizeFields, 1)
            ->andReturn($userDetail);

        $userService
            ->shouldReceive('unsetPseudonymizedFields')
            ->once()
            ->with($request->all())
            ->andReturn($notPseudonymizeFields);

        $userService
            ->shouldReceive('updateSkill')
            ->once()
            ->andReturn([['skill_id' => 1]]);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);

        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($userDetail);

        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_UPDATED'),
                ['user_id' => 1]
            );

        $this->expectsEvents(UserActivityLogEvent::class);

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            null,
            $userService
        );
        $response = $controller->update($request, 1);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test update success with pseudonymized_at field scenario
    *
    * @return void
    */
    public function testUpdateSuccessWithPseudonymizeAtField()
    {
        $mergeData = [
            'avatar' => null,
            'expiry' => null
        ];
        $exceptData = [
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null
        ];
        $data = [
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]],
            'department' => 'The Department',
            'employee_id' => 123,
            'pseudonymize_at' => '0000-00-00 00:00:00',
            'status' => 1
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData);

        $userService = $this->mock(UserService::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $userRepository = $this->mock(UserRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all(), 1)
            ->andReturn(true);

        $userDetail = new User();
        $userDetail->setAttribute('pseudonymize_at', '0000-00-00 00:00:00');
        $userDetail->setAttribute('user_id', 1);

        $userService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($userDetail);

        $userService
            ->shouldReceive('update')
            ->once()
            ->with(array_merge($request->all(), ['status' => 0]), 1)
            ->andReturn($userDetail);

        $userService
            ->shouldReceive('updateSkill')
            ->once()
            ->andReturn([['skill_id' => 1]]);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);
        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($userDetail);

        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_UPDATED'),
                ['user_id' => 1]
            );

        $this->expectsEvents(UserActivityLogEvent::class);

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            $timezoneRepository
        );
        $response = $controller->update($request, 1);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test update invalid validation scenario
    *
    * @return void
    */
    public function testUpdateInvalidValidation()
    {
        $mergeData = [
            'avatar' => null,
            'expiry' => null
        ];
        $exceptData = [
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null
        ];
        $data = [
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]]
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData);

        $userService = $this->mock(UserService::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $userRepository = $this->mock(UserRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all(), 1)
            ->andReturn(new JsonResponse);

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            $timezoneRepository
        );
        $response = $controller->update($request, 1);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test update invalid language ID scenario
    *
    * @return void
    */
    public function testUpdateInvalidLanguageId()
    {
        $mergeData = [
            'avatar' => null,
            'expiry' => null
        ];
        $exceptData = [
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null
        ];
        $data = [
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]]
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData);

        $userService = $this->mock(UserService::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $userRepository = $this->mock(UserRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all(), 1)
            ->andReturn(true);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(false);
        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                trans('messages.custom_error_message.ERROR_USER_INVALID_LANGUAGE')
            );

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            $timezoneRepository
        );
        $response = $controller->update($request, 1);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test update will throw exception scenario
    *
    * @return void
    */
    public function testUpdateInvalidThrowException()
    {
        $mergeData = [
            'avatar' => null,
            'expiry' => null
        ];
        $exceptData = [
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null
        ];
        $data = [
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]],
            'status' => 1
        ];

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData);

        $userService = $this->mock(UserService::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $userRepository = $this->mock(UserRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all(), 1)
            ->andReturn(true);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);

        $userService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andThrow(ModelNotFoundException::class);

        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );

        $controller = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            null,
            $timezoneRepository
        );
        $response = $controller->update($request, 1);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /*
    * @testdox Test store user with required fields
    *
    * @return void
    */
    public function testStoreUserWithRequiredFieldsOnlySuccess()
    {
        $this->expectsEvents(UserActivityLogEvent::class);
        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->shouldReceive('remove')
            ->andReturn(true);
        $exceptData = [
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null
        ];

        $request = $this->mock(Request::class);
        $request->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'Passw0rd'
            ])
            ->shouldReceive('toArray')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'Passw0rd'
            ])
            ->shouldReceive('merge')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'Passw0rd',
                'timezone_id' => 1
            ])
            ->shouldReceive('except')
            ->andReturn($exceptData);
        $request->request = $symfonyRequest;
        $request->skills = null;
        $request->language_id = null;
        $request->expiry = null;
        $request->timezone_id = null;
        $request->status = null;

        $timezone = new Timezone();
        $timezone->setAttribute('timezone_id', 1);

        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $timezoneRepository
            ->shouldReceive('getTenantTimezoneByCode')
            ->once()
            ->with('Europe/Paris')
            ->andReturn($timezone);

        $languageHelper = $this->mock(LanguageHelper::class);
        $languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($request)
            ->andReturn((object) [
                'language_id' => $exceptData['language_id']
            ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = new User();
        $user->setAttribute('user_id', 1);

        $methodResponse = [
            'user_id' => 1
        ];

        $userService = $this->mock(UserService::class);
        $userService
            ->shouldReceive('store')
            ->once()
            ->with($request->toArray())
            ->andReturn($user);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all())
            ->andReturn(true);

        $jsonResponse = new JsonResponse(
            $methodResponse,
            Response::HTTP_CREATED
        );

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_CREATED,
                trans('messages.success.MESSAGE_USER_CREATED'),
                $methodResponse
            )
            ->andReturn($jsonResponse);
        $notificationRepository = $this->mock(NotificationRepository::class);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($user);

        $service = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            $notificationRepository,
            $timezoneRepository
        );

        $response = $service->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(
            $methodResponse,
            json_decode($response->getContent(), true)
        );
    }

    /**
    * @testdox Test store user
    *
    * @return void
    */
    public function testStoreUserSuccess()
    {
        $this->expectsEvents(UserActivityLogEvent::class);
        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->shouldReceive('remove')
            ->andReturn(true);
        $request = $this->mock(Request::class);
        $request->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'Passw0rd',
                'first_name' => 'Test',
                'last_name' => 'Email'
            ])
            ->shouldReceive('toArray')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'Passw0rd',
                'first_name' => 'Test',
                'last_name' => 'Email'
            ])
            ->shouldReceive('merge')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'Passw0rd',
                'timezone_id' => 1
            ])
            ->shouldReceive('except')
            ->andReturn([
                'email' => 'testemail@yahoo.com'
            ]);
        $request->request = $symfonyRequest;
        $request->skills = [['skill_id' => 1]];
        $request->language_id = 1;
        $request->expiry = null;
        $request->timezone_id = null;
        $request->status = null;

        $timezone = new Timezone();
        $timezone->setAttribute('timezone_id', 1);

        $timezoneRepository = $this->mock(TimezoneRepository::class);
        $timezoneRepository
            ->shouldReceive('getTenantTimezoneByCode')
            ->once()
            ->with('Europe/Paris')
            ->andReturn($timezone);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = new User();
        $user->setAttribute('user_id', 1);

        $methodResponse = [
            'user_id' => 1
        ];

        $userService = $this->mock(UserService::class);
        $userService
            ->shouldReceive('store')
            ->once()
            ->with($request->toArray())
            ->andReturn($user);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all())
            ->andReturn(true);

        $jsonResponse = new JsonResponse(
            $methodResponse,
            Response::HTTP_CREATED
        );

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_CREATED,
                trans('messages.success.MESSAGE_USER_CREATED'),
                $methodResponse
            )
            ->andReturn($jsonResponse);
        $notificationRepository = $this->mock(NotificationRepository::class);

        $languageHelper = $this->mock(LanguageHelper::class);
        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($user);

        $userService
            ->shouldReceive('linkSkill')
            ->once()
            ->with([
                'email' => 'testemail@yahoo.com'
            ], 1)
            ->andReturn([true]);

        $service = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            $notificationRepository,
            $timezoneRepository
        );

        $response = $service->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(
            $methodResponse,
            json_decode($response->getContent(), true)
        );
    }

    /**
    * @testdox Test store user invalid password
    *
    * @return void
    */
    public function testStoreUserPasswordInvalid()
    {
        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->shouldReceive('remove')
            ->andReturn(true);
        $request = $this->mock(Request::class);
        $request->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'password',
                'first_name' => 'Test',
                'last_name' => 'Email'
            ])
            ->shouldReceive('toArray')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'password',
                'first_name' => 'Test',
                'last_name' => 'Email'
            ]);
        $request->request = $symfonyRequest;
        $request->skills = [['skill_id' => 1]];
        $request->language_id = 1;
        $request->expiry = null;

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $errors = new Collection([
            trans('messages.custom_error_message.ERROR_PASSWORD_VALIDATION_MESSAGE')
        ]);
        $validator->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = new User();
        $user->setAttribute('user_id', 1);

        $userService = $this->mock(UserService::class);
        $userService
            ->shouldReceive('store')
            ->never()
            ->with($request->toArray())
            ->andReturn($user);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all())
            ->andReturn(new JsonResponse);

        $responseHelper = $this->mock(ResponseHelper::class);
        $notificationRepository = $this->mock(NotificationRepository::class);

        $languageHelper = $this->mock(LanguageHelper::class);
        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->never()
            ->with($request)
            ->andReturn(true);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->never()
            ->with(1, $request)
            ->andReturn($user);

        $userRepository
            ->shouldReceive('linkSkill')
            ->never()
            ->with($request->toArray(), 1)
            ->andReturn([true]);

        $service = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            $notificationRepository
        );

        $response = $service->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test store user invalid password
    *
    * @return void
    */
    public function testStoreUserLanguageIdInvalid()
    {
        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->shouldReceive('remove')
            ->andReturn(true);
        $request = $this->mock(Request::class);
        $request->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'password',
                'first_name' => 'Test',
                'last_name' => 'Email'
            ])
            ->shouldReceive('toArray')
            ->andReturn([
                'email' => 'testemail@yahoo.com',
                'password' => 'password',
                'first_name' => 'Test',
                'last_name' => 'Email'
            ]);
        $request->request = $symfonyRequest;
        $request->skills = [['skill_id' => 1]];
        $request->language_id = 1;
        $request->expiry = null;

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = new User();
        $user->setAttribute('user_id', 1);

        $methodResponse = [
            'errors' => [
                'message' => trans('messages.custom_error_message.ERROR_USER_INVALID_LANGUAGE')
            ]
        ];

        $userService = $this->mock(UserService::class);
        $userService
            ->shouldReceive('store')
            ->never()
            ->with($request->toArray())
            ->andReturn($user);

        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all())
            ->andReturn(true);

        $jsonResponse = new JsonResponse(
            $methodResponse,
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                trans('messages.custom_error_message.ERROR_USER_INVALID_LANGUAGE')
            )
            ->andReturn($jsonResponse);

        $notificationRepository = $this->mock(NotificationRepository::class);

        $languageHelper = $this->mock(LanguageHelper::class);
        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(false);

        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->never()
            ->with(1, $request)
            ->andReturn($user);

        $userRepository
            ->shouldReceive('linkSkill')
            ->never()
            ->with($request->toArray(), 1)
            ->andReturn([true]);

        $service = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            null,
            $request,
            $notificationRepository
        );

        $response = $service->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(
            $methodResponse,
            json_decode($response->getContent(), true)
        );
    }

    /**
    * @testdox Test update user
    *
    * @return void
    */
    public function testUpdateUserSuccess()
    {
        $this->expectsEvents(UserActivityLogEvent::class);
        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->shouldReceive('remove')
            ->andReturn(true);
        $request = $this->mock(Request::class);
        $request->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn([
                'password' => 'Passw0rd'
            ])
            ->shouldReceive('toArray')
            ->andReturn([
                'password' => 'Passw0rd'
            ])
            ->shouldReceive('merge')
            ->andReturn([
                'password' => 'Passw0rd'
            ])
            ->shouldReceive('except')
            ->andReturn([
                'password' => 'Passw0rd'
            ]);
        $request->request = $symfonyRequest;
        $request->skills = null;
        $request->language_id = null;
        $request->expiry = null;
        $request->status = null;
        $request->avatar = null;

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = new User();
        $user->setAttribute('user_id', 1);
        $user->setAttribute('pseudonymize_at', null);

        $methodResponse = [
            'user_id' => 1
        ];

        $userService = $this->mock(UserService::class);
        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all(), 1)
            ->andReturn(true);

        $userService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $jsonResponse = new JsonResponse(
            $methodResponse,
            Response::HTTP_OK
        );

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_UPDATED'),
                $methodResponse
            )
            ->andReturn($jsonResponse);
        $notificationRepository = $this->mock(NotificationRepository::class);

        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userRepository = $this->mock(UserRepository::class);

        $userService
            ->shouldReceive('update')
            ->once()
            ->with(['password' => 'Passw0rd'], 1)
            ->andReturn($user);

        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($user);

        $service = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            $helpers,
            $request,
            $notificationRepository
        );

        $response = $service->update($request, 1);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(
            $methodResponse,
            json_decode($response->getContent(), true)
        );
    }

    /**
    * @testdox Test update user invalid password
    *
    * @return void
    */
    public function testUpdateUserInvalidPassword()
    {
        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->shouldReceive('remove')
            ->andReturn(true);
        $request = $this->mock(Request::class);
        $request->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn([
                'password' => 'password'
            ])
            ->shouldReceive('toArray')
            ->andReturn([
                'password' => 'password'
            ]);
        $request->request = $symfonyRequest;
        $request->skills = null;
        $request->language_id = null;
        $request->expiry = null;
        $request->status = null;

        $errors = new Collection([
            trans('messages.custom_error_message.ERROR_PASSWORD_VALIDATION_MESSAGE')
        ]);
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator
            ->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = new User();
        $user->setAttribute('user_id', 1);
        $user->setAttribute('pseudonymize_at', null);

        $methodResponse = [
            'user_id' => 1
        ];

        $userService = $this->mock(UserService::class);
        $userService
            ->shouldReceive('validateFields')
            ->once()
            ->with($request->all(), 1)
            ->andReturn(new JsonResponse);

        $responseHelper = $this->mock(ResponseHelper::class);
        $notificationRepository = $this->mock(NotificationRepository::class);

        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userRepository = $this->mock(UserRepository::class);
        $userRepository
            ->shouldReceive('find')
            ->never()
            ->with(1)
            ->andReturn($user);

        $userRepository
            ->shouldReceive('update')
            ->never()
            ->with(['password' => 'Passw0rd', 'expiry' => null], 1)
            ->andReturn($user);

        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->never()
            ->with(1, $request)
            ->andReturn($user);

        $service = $this->getController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            null,
            $helpers,
            $request,
            $notificationRepository
        );

        $response = $service->update($request, 1);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Create a new service instance.
     *
     * @param  App\Services\UserService $userService
     *
     * @return void
     */
    private function getController(
        UserRepository $userRepository = null,
        ResponseHelper $responseHelper = null,
        LanguageHelper $languageHelper = null,
        UserService $userService = null,
        TimesheetService $timesheetService = null,
        Helpers $helpers = null,
        Request $request,
        NotificationRepository $notificationRepository = null,
        TimezoneRepository $timezoneRepository = null
    ) {
        $userRepository = $userRepository ?? $this->mock(UserRepository::class);
        $responseHelper = $responseHelper ?? $this->mock(ResponseHelper::class);
        $languageHelper = $languageHelper ?? $this->mock(LanguageHelper::class);
        $userService = $userService ?? $this->mock(UserService::class);
        $timesheetService = $timesheetService ?? $this->mock(TimesheetService::class);
        $helpers = $helpers ?? $this->mock(Helpers::class);
        $notificationRepository = $notificationRepository ?? $this->mock(NotificationRepository::class);
        $timezoneRepository = $timezoneRepository ?? $this->mock(TimezoneRepository::class);

        return new UserController(
            $userRepository,
            $responseHelper,
            $languageHelper,
            $userService,
            $timesheetService,
            $helpers,
            $request,
            $notificationRepository,
            $timezoneRepository
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
