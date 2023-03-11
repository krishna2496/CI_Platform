<?php

namespace Tests\Unit\Http\Controllers\App\Auth;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\S3Helper;
use App\Http\Controllers\App\User\UserController;
use App\Models\TenantOption;
use App\Repositories\City\CityRepository;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\UserCustomField\UserCustomFieldRepository;
use App\Repositories\UserFilter\UserFilterRepository;
use App\Repositories\User\UserRepository;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\Response;
use Mockery;
use TestCase;
use App\Models\UserFilter;
use App\Events\User\UserActivityLogEvent;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use Validator;

class UserControllerTest extends TestCase
{
    const OPTION_NAME_SSO = 'saml_settings';

    public function testInviteUser()
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('toArray')
            ->andReturn([
                'email' => 'test@optimy.com',
                'subject' => 'Notification',
                'body' => 'body',
                'language' => 'en'
            ])
            ->shouldReceive('get')
            ->andReturn('test@optimy.com')
            ->shouldReceive('only');

        $userModel = $this->mock(User::class);
        $userModel->shouldReceive('getAttribute')
            ->shouldReceive('notify')
            ->shouldReceive('setAttribute')
            ->shouldReceive('save');

        $userRepository = $this->mock(UserRepository::class);
        $userRepository->shouldReceive('findUserByEmail')
            ->andReturn($userModel);

        $userCustomFieldRepository = $this->mock(UserCustomFieldRepository::class);
        $cityRepository = $this->mock(CityRepository::class);
        $userFilterRepository = $this->mock(UserFilterRepository::class);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success');

        $languageHelper = $this->mock(LanguageHelper::class);

        $helpers = $this->mock(Helpers::class);

        $s3Helper = $this->mock(S3Helper::class);

        $samlSettings = new Collection([
            0 => [
                'option_value' => [
                    'saml_access_only' => false
                ]
            ]
        ]);

        $tenantOption = $this->mock(TenantOption::class);
        $tenantOption->shouldReceive('getAttribute');

        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);
        $tenantOptionRepository->shouldReceive('getOptionValue')
            ->with(self::OPTION_NAME_SSO)
            ->once()
            ->andReturn($samlSettings)
            ->shouldReceive('getOptionValueFromOptionName')
            ->andReturn($tenantOption);

        $userService = $this->mock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $userController = new UserController(
            $userRepository,
            $userCustomFieldRepository,
            $cityRepository,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            $s3Helper,
            $tenantOptionRepository,
            $userService,
            $tenantActivatedSettingRepository
        );

        $this->withoutEvents();

        $response = $userController->inviteUser($userModel, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testCreatePassword()
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('toArray')
            ->andReturn([
                'email' => 'test@optimy.com',
                'password' => 'YwE$#12dW'
            ])
            ->shouldReceive('get')
            ->andReturn('test@optimy.com')
            ->shouldReceive('only');

        $userModel = $this->mock(User::class);
        $userModel->shouldReceive('getAttribute')
            ->shouldReceive('setAttribute')
            ->shouldReceive('save');

        $userRepository = $this->mock(UserRepository::class);
        $userRepository->shouldReceive('findUserByEmail')
            ->andReturn($userModel);

        $userCustomFieldRepository = $this->mock(UserCustomFieldRepository::class);
        $cityRepository = $this->mock(CityRepository::class);
        $userFilterRepository = $this->mock(UserFilterRepository::class);

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success');

        $languageHelper = $this->mock(LanguageHelper::class);

        $helpers = $this->mock(Helpers::class);

        $s3Helper = $this->mock(S3Helper::class);

        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);

        $userService = $this->mock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $userController = new UserController(
            $userRepository,
            $userCustomFieldRepository,
            $cityRepository,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            $s3Helper,
            $tenantOptionRepository,
            $userService,
            $tenantActivatedSettingRepository
        );

        $this->withoutEvents();

        $response = $userController->createPassword($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

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
            'status' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]]
        ];

        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->user_id = 1;
        $symfonyRequest->email = 'testuser@email.com';

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData)
            ->shouldReceive('replace')
            ->andReturn($exceptData);
        $request->auth = $symfonyRequest;

        $userRepository = $this->mock(UserRepository::class);
        $userCustomFieldRepository = $this->mock(UserCustomFieldRepository::class);
        $userFilterRepository = $this->mock(UserFilterRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userService = $this->mock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $user = new User();
        $user->setAttribute('pseudonymize_at', null);
        $user->setAttribute('user_id', 1);
        $user->setAttribute('is_profile_complete', 1);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(false);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);

        $userFilterRepository
            ->shouldReceive('saveFilter')
            ->once()
            ->with($request)
            ->andReturn(new UserFilter);

        $userService
            ->shouldReceive('update')
            ->once()
            ->with($request->all(), 1)
            ->andReturn($user);

        $userService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($user);

        $userService
            ->shouldReceive('updateSkill')
            ->once()
            ->with($request->all(), 1)
            ->andReturn(true);

        $helpers
            ->shouldReceive('syncUserData')
            ->once()
            ->with($request, $user)
            ->andReturn(true);

        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_UPDATED'),
                ['user_id' => 1, 'is_profile_complete' => 1]
            );

        $this->expectsEvents(UserActivityLogEvent::class);

        $controller = $this->getController(
            $userRepository,
            $userCustomFieldRepository,
            null,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            null,
            null,
            $userService,
            $tenantActivatedSettingRepository
        );

        $response = $controller->update($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

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

        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->user_id = 1;
        $symfonyRequest->email = 'testuser@email.com';

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData)
            ->shouldReceive('replace')
            ->andReturn($exceptData);
        $request->auth = $symfonyRequest;

        $userRepository = $this->mock(UserRepository::class);
        $userCustomFieldRepository = $this->mock(UserCustomFieldRepository::class);
        $userFilterRepository = $this->mock(UserFilterRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userService = $this->mock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $user = new User();
        $user->setAttribute('pseudonymize_at', null);
        $user->setAttribute('user_id', 1);
        $user->setAttribute('is_profile_complete', 1);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(true);

        $errors = new Collection([
            'sample-error message'
        ]);
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                $errors->first()
            );

        Validator::shouldReceive('make')
            ->andReturn($validator);


        $this->withoutEvents();
        $controller = $this->getController(
            $userRepository,
            $userCustomFieldRepository,
            null,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            null,
            null,
            $userService,
            $tenantActivatedSettingRepository
        );

        $response = $controller->update($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

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

        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->user_id = 1;
        $symfonyRequest->email = 'testuser@email.com';

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData)
            ->shouldReceive('replace')
            ->andReturn($exceptData);
        $request->auth = $symfonyRequest;

        $userRepository = $this->mock(UserRepository::class);
        $userCustomFieldRepository = $this->mock(UserCustomFieldRepository::class);
        $userFilterRepository = $this->mock(UserFilterRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userService = $this->mock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $user = new User();
        $user->setAttribute('pseudonymize_at', null);
        $user->setAttribute('user_id', 1);
        $user->setAttribute('is_profile_complete', 1);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(false);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

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

        $this->withoutEvents();

        $controller = $this->getController(
            $userRepository,
            $userCustomFieldRepository,
            null,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            null,
            null,
            $userService,
            $tenantActivatedSettingRepository
        );

        $response = $controller->update($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUpdateInvalidMaximumSkill()
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
            'skills' => [
                ['skill_id' => 1],
                ['skill_id' => 2],
                ['skill_id' => 3],
                ['skill_id' => 4],
                ['skill_id' => 5],
                ['skill_id' => 6],
                ['skill_id' => 7],
                ['skill_id' => 8],
                ['skill_id' => 9],
                ['skill_id' => 10],
                ['skill_id' => 11],
                ['skill_id' => 12],
                ['skill_id' => 13],
                ['skill_id' => 14],
                ['skill_id' => 15],
                ['skill_id' => 16]
            ]
        ];

        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->user_id = 1;
        $symfonyRequest->email = 'testuser@email.com';

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData)
            ->shouldReceive('replace')
            ->andReturn($exceptData);
        $request->auth = $symfonyRequest;

        $userRepository = $this->mock(UserRepository::class);
        $userCustomFieldRepository = $this->mock(UserCustomFieldRepository::class);
        $userFilterRepository = $this->mock(UserFilterRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userService = $this->mock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $user = new User();
        $user->setAttribute('pseudonymize_at', null);
        $user->setAttribute('user_id', 1);
        $user->setAttribute('is_profile_complete', 1);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(false);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);

        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_SKILL_LIMIT'),
                trans('messages.custom_error_message.ERROR_SKILL_LIMIT')
            );

        $this->withoutEvents();

        $controller = $this->getController(
            $userRepository,
            $userCustomFieldRepository,
            null,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            null,
            null,
            $userService,
            $tenantActivatedSettingRepository
        );

        $response = $controller->update($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUpdateSuccessWithPseudonymizedUserData()
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
            'status' => null,
            'skills' => [['skill_id' => 1]],
            'department' => 'The Department',
            'employee_id' => 123
        ];
        $notPseudonymizeFields = [
            'password' => 'Qwerty1234',
            'language_id' => 1,
            'avatar' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]]
        ];

        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->user_id = 1;
        $symfonyRequest->email = 'testuser@email.com';

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData)
            ->shouldReceive('replace')
            ->andReturn($exceptData);
        $request->auth = $symfonyRequest;

        $userRepository = $this->mock(UserRepository::class);
        $userCustomFieldRepository = $this->mock(UserCustomFieldRepository::class);
        $userFilterRepository = $this->mock(UserFilterRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userService = $this->mock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $user = new User();
        $user->setAttribute('pseudonymize_at', '2020-10-02 12:32:29.0');
        $user->setAttribute('user_id', 1);
        $user->setAttribute('is_profile_complete', 1);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(false);

        $userService
            ->shouldReceive('unsetPseudonymizedFields')
            ->once()
            ->with($request->all())
            ->andReturn($notPseudonymizeFields);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);

        $userFilterRepository
            ->shouldReceive('saveFilter')
            ->once()
            ->with($request)
            ->andReturn(new UserFilter);

        $userService
            ->shouldReceive('update')
            ->once()
            ->with($notPseudonymizeFields, 1)
            ->andReturn($user);

        $userService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($user);

        $userService
            ->shouldReceive('updateSkill')
            ->once()
            ->with($notPseudonymizeFields, 1)
            ->andReturn(true);

        $helpers
            ->shouldReceive('syncUserData')
            ->once()
            ->with($request, $user)
            ->andReturn(true);

        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_UPDATED'),
                ['user_id' => 1, 'is_profile_complete' => 1]
            );

        $this->expectsEvents(UserActivityLogEvent::class);

        $controller = $this->getController(
            $userRepository,
            $userCustomFieldRepository,
            null,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            null,
            null,
            $userService,
            $tenantActivatedSettingRepository
        );

        $response = $controller->update($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUpdateSuccessWithPseudonymizedAtField()
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
            'status' => null,
            'expiry' => null,
            'skills' => [['skill_id' => 1]],
            'department' => 'The Department',
            'employee_id' => 123,
            'pseudonymize_at' => '0000-00-00 00:00:00'
        ];

        $symfonyRequest = $this->mock(SymfonyRequest::class);
        $symfonyRequest->user_id = 1;
        $symfonyRequest->email = 'testuser@email.com';

        $request = $this->mock(Request::class);
        $request
            ->shouldReceive('header')
            ->shouldReceive('all')
            ->andReturn($data)
            ->shouldReceive('merge')
            ->andReturn(array_merge($data, $mergeData))
            ->shouldReceive('except')
            ->andReturn($exceptData)
            ->shouldReceive('replace')
            ->andReturn($exceptData);
        $request->auth = $symfonyRequest;

        $userRepository = $this->mock(UserRepository::class);
        $userCustomFieldRepository = $this->mock(UserCustomFieldRepository::class);
        $userFilterRepository = $this->mock(UserFilterRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userService = $this->mock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $user = new User();
        $user->setAttribute('pseudonymize_at', '0000-00-00 00:00:00');
        $user->setAttribute('user_id', 1);
        $user->setAttribute('is_profile_complete', 1);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(false);

        $languageHelper
            ->shouldReceive('validateLanguageId')
            ->once()
            ->with($request)
            ->andReturn(true);

        $userFilterRepository
            ->shouldReceive('saveFilter')
            ->once()
            ->with($request)
            ->andReturn(new UserFilter);

        $userService
            ->shouldReceive('update')
            ->once()
            ->with(array_merge($request->all(), ['status' => 0]), 1)
            ->andReturn($user);

        $userService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $userRepository
            ->shouldReceive('checkProfileCompleteStatus')
            ->once()
            ->with(1, $request)
            ->andReturn($user);

        $userService
            ->shouldReceive('updateSkill')
            ->once()
            ->with(array_merge($request->all(), ['status' => 0]), 1)
            ->andReturn(true);

        $helpers
            ->shouldReceive('syncUserData')
            ->once()
            ->with($request, $user)
            ->andReturn(true);

        $responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_UPDATED'),
                ['user_id' => 1, 'is_profile_complete' => 1]
            );

        $this->expectsEvents(UserActivityLogEvent::class);

        $controller = $this->getController(
            $userRepository,
            $userCustomFieldRepository,
            null,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            null,
            null,
            $userService,
            $tenantActivatedSettingRepository
        );

        $response = $controller->update($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    private function getController(
        UserRepository $userRepository = null,
        UserCustomFieldRepository $userCustomFieldRepository = null,
        CityRepository $cityRepository = null,
        UserFilterRepository $userFilterRepository = null,
        ResponseHelper $responseHelper = null,
        LanguageHelper $languageHelper = null,
        Helpers $helpers = null,
        S3Helper $s3Helper = null,
        TenantOptionRepository $tenantOptionRepository = null,
        UserService $userService = null,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository = null
    ) {
        $userRepository = $userRepository ?? $this->mock(UserRepository::class);
        $userCustomFieldRepository = $userCustomFieldRepository ?? $this->mock(UserCustomFieldRepository::class);
        $cityRepository = $cityRepository ?? $this->mock(CityRepository::class);
        $userFilterRepository = $userFilterRepository ?? $this->mock(UserFilterRepository::class);
        $responseHelper = $responseHelper ?? $this->mock(ResponseHelper::class);
        $languageHelper = $languageHelper ?? $this->mock(LanguageHelper::class);
        $helpers = $helpers ?? $this->mock(Helpers::class);
        $s3Helper = $s3Helper ?? $this->mock(S3Helper::class);
        $tenantOptionRepository = $tenantOptionRepository ?? $this->mock(TenantOptionRepository::class);
        $userService = $userService ??$this->mock(UserService::class);
        $tenantActivatedSettingRepository = $tenantActivatedSettingRepository ?? $this->mock(TenantActivatedSettingRepository::class);

        return new UserController(
            $userRepository,
            $userCustomFieldRepository,
            $cityRepository,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            $s3Helper,
            $tenantOptionRepository,
            $userService,
            $tenantActivatedSettingRepository
        );
    }

    public function testIndexSearchUsers()
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userCustomFieldRepository = $this->createMock(UserCustomFieldRepository::class);
        $responseHelper = $this->createMock(ResponseHelper::class);
        $languageHelper = $this->createMock(LanguageHelper::class);
        $helpers = $this->createMock(Helpers::class);
        $s3helper = $this->createMock(S3Helper::class);
        $userFilterRepository = $this->createMock(UserFilterRepository::class);
        $tenantOptionRepository = $this->createMock(TenantOptionRepository::class);
        $cityRepository = $this->createMock(CityRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $controller = new UserController(
            $userRepository,
            $userCustomFieldRepository,
            $cityRepository,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            $s3helper,
            $tenantOptionRepository,
            $this->createMock(UserService::class),
            $tenantActivatedSettingRepository
        );

        $request = new Request(['search' => 'jeannot']);
        $request->auth = new \stdClass();
        $request->auth->user_id = 1;

        $user1 = new User(['first_name' => 'Jeannot', 'last_name' => 'Lapin', 'avatar' => 'default.png']);
        $user1->user_id = 1;

        $userCollection = new Collection([
            $user1
        ]);

        $userRepository
            ->expects($this->never())
            ->method('listUsers');

        $userRepository
            ->expects($this->once())
            ->method('searchUsers')
            ->willReturn($userCollection);

        $helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest')
            ->with($request)
            ->willReturn('ci-api');

        $responseHelper
            ->expects($this->once())
            ->method('success')
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_LISTING'),
                [
                    $user1
                ]
            )
            ->willReturn(new JsonResponse());

        $result = $controller->index($request);

        // testing the mock to avoid warning in phpunit
        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    public function testIndexGetAllUsers()
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userCustomFieldRepository = $this->createMock(UserCustomFieldRepository::class);
        $responseHelper = $this->createMock(ResponseHelper::class);
        $languageHelper = $this->createMock(LanguageHelper::class);
        $helpers = $this->createMock(Helpers::class);
        $s3helper = $this->createMock(S3Helper::class);
        $userFilterRepository = $this->createMock(UserFilterRepository::class);
        $tenantOptionRepository = $this->createMock(TenantOptionRepository::class);
        $cityRepository = $this->createMock(CityRepository::class);
        $userService = $this->createMock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $controller = new UserController(
            $userRepository,
            $userCustomFieldRepository,
            $cityRepository,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            $s3helper,
            $tenantOptionRepository,
            $userService,
            $tenantActivatedSettingRepository
        );

        $request = new Request();
        $request->auth = new \stdClass();
        $request->auth->user_id = 1;

        $user1 = new User(['first_name' => 'Jeannot', 'last_name' => 'Lapin', 'avatar' => 'default.png']);
        $user1->user_id = 1;
        $user2 = new User(['first_name' => 'Daisy', 'last_name' => 'Duck', 'avatar' => 'default.png']);
        $user2->user_id = 2;
        $user3 = new User(['first_name' => 'Mickey', 'last_name' => 'Mouse', 'avatar' => 'default.png']);
        $user3->user_id = 3;

        $userCollection = new Collection([
            $user1,
            $user2,
            $user3
        ]);

        $userRepository
            ->expects($this->once())
            ->method('listUsers')
            ->willReturn($userCollection);

        $userRepository
            ->expects($this->never())
            ->method('searchUsers');

        $helpers
            ->expects($this->once())
            ->method('getSubDomainFromRequest')
            ->with($request)
            ->willReturn('ci-api');

        $responseHelper
            ->expects($this->once())
            ->method('success')
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_LISTING'),
                [
                    $user1,
                    $user2,
                    $user3
                ]
            )
            ->willReturn(new JsonResponse());

        $result = $controller->index($request);

        // testing the mock to avoid warning in phpunit
        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    /**
    * @testdox Test create password with invalid password
    *
    * @return void
    */
    public function testCreatePasswordInvalidPassword()
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('toArray')
            ->andReturn([
                'email' => 'test@optimy.com',
                'password' => 'password'
            ])
            ->shouldReceive('get')
            ->andReturn('test@optimy.com')
            ->shouldReceive('only');

        $userModel = $this->mock(User::class);
        $userModel->shouldReceive('getAttribute')
            ->shouldReceive('setAttribute')
            ->shouldReceive('save');

        $userRepository = $this->mock(UserRepository::class);
        $userRepository->shouldReceive('findUserByEmail')
            ->andReturn($userModel);

        $userCustomFieldRepository = $this->mock(UserCustomFieldRepository::class);
        $cityRepository = $this->mock(CityRepository::class);
        $userFilterRepository = $this->mock(UserFilterRepository::class);

        $jsonResponse = new JsonResponse(
            [
                'errors' => [
                    'messages' => trans('messages.custom_error_message.ERROR_PASSWORD_VALIDATION_MESSAGE')
                ]
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_DETAIL'),
                trans('messages.custom_error_message.ERROR_PASSWORD_VALIDATION_MESSAGE')
            )
            ->andReturn($jsonResponse);

        $languageHelper = $this->mock(LanguageHelper::class);

        $helpers = $this->mock(Helpers::class);

        $s3Helper = $this->mock(S3Helper::class);

        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);

        $userService = $this->mock(UserService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $userController = new UserController(
            $userRepository,
            $userCustomFieldRepository,
            $cityRepository,
            $userFilterRepository,
            $responseHelper,
            $languageHelper,
            $helpers,
            $s3Helper,
            $tenantOptionRepository,
            $userService,
            $tenantActivatedSettingRepository
        );

        $this->withoutEvents();

        $response = $userController->createPassword($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
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
