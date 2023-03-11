<?php

namespace Tests\Unit\Http\Controllers\App\Auth;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Repositories\User\UserRepository;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use TestCase;
use App\Http\Controllers\App\UserSetting\UserSettingController;
use App\Repositories\UserSetting\UserSettingRepository;
use App\Repositories\Timezone\TimezoneRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use Validator;

class UserSettingControllerTest extends TestCase
{
    public function testStoreSuccess()
    {
        $this->expectsEvents(\App\Events\User\UserActivityLogEvent::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userRepository = $this->mock(UserRepository::class);
        $userSettingRepository = $this->mock(UserSettingRepository::class);
        $timeZoneRepository = $this->mock(TimezoneRepository::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $payload = [
            'language_id' => 1,
            'timezone_id' => 1,
            'currency' => 'INR',
            'auth' => (object)[
                'user_id' => rand(),
                'email' => 'test@email.com'
            ]
        ];

        $usersData = [
            'language_id' => 1,
            'timezone_id' => 1,
            'currency' => 'INR',
        ];


        $request = new Request($payload);

        $tenant = [
            'tenant_id' => 1
        ];

        $userSettingController = new UserSettingController(
            $responseHelper,
            $helpers,
            $userRepository,
            $userSettingRepository,
            $timeZoneRepository,
            $languageHelper,
            $tenantActivatedSettingRepository
        );

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(true);

        $helpers->shouldReceive('getTenantIdAndSponsorIdFromRequest')
            ->once()
            ->andReturn((object)$tenant);

        $userSettingRepository->shouldReceive('saveUserData')
            ->once()
            ->with($request->auth->user_id, $usersData)
            ->andReturn();

        $responseHelper->shouldReceive('success')
            ->once()
            ->andReturn();

        $response = $userSettingController->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testStoreFailure()
    {
        $responseHelper = $this->mock(ResponseHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userRepository = $this->mock(UserRepository::class);
        $userSettingRepository = $this->mock(UserSettingRepository::class);
        $timeZoneRepository = $this->mock(TimezoneRepository::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $payload = [
            'language_id' => 1,
            'timezone_id' => 1,
            'currency' => 'INRT',
            'auth' => (object) [
                'user_id' => 1
            ]
        ];
        $request = new Request($payload);
        $tenant = [
            'tenant_id' => 1
        ];

        $userSettingController = new UserSettingController(
            $responseHelper,
            $helpers,
            $userRepository,
            $userSettingRepository,
            $timeZoneRepository,
            $languageHelper,
            $tenantActivatedSettingRepository
        );

        $helpers->shouldReceive('getTenantIdAndSponsorIdFromRequest')
            ->once()
            ->andReturn((object)$tenant);

        $errors = new Collection([
            config('constants.error_codes.ERROR_INVALID_DETAIL')
        ]);
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);
        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(true);

        $responseHelper->shouldReceive('error')
            ->once()
            ->andReturn();

        $response = $userSettingController->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testIndexUserSettingSuccess()
    {
        $responseHelper = $this->mock(ResponseHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userRepository = $this->mock(UserRepository::class);
        $userSettingRepository = $this->mock(UserSettingRepository::class);
        $timeZoneRepository = $this->mock(TimezoneRepository::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $data = [
            'auth' => (object)[
                'user_id' => 1
            ]
        ];
        $request = new Request($data);

        $userSettingController = new UserSettingController(
            $responseHelper,
            $helpers,
            $userRepository,
            $userSettingRepository,
            $timeZoneRepository,
            $languageHelper,
            $tenantActivatedSettingRepository
        );

        $userSettingRepository->shouldReceive('getUserPreferenceData')
            ->once()
            ->with($request->auth->user_id)
            ->andReturn();

        $timeZoneRepository->shouldReceive('getTimezoneList')
            ->once()
            ->andReturn(new Collection());

        $languageHelper->shouldReceive('getTenantLanguages')
            ->once()
            ->with($request)
            ->andReturn();

        $helpers->shouldReceive('getTenantActivatedCurrencies')
            ->once()
            ->with($request)
            ->andReturn(new Collection());

        $userRepository->shouldReceive('findUserDetail')
            ->once()
            ->with(1)
            ->andReturn(new User());

        $responseHelper->shouldReceive('success')
            ->once()
            ->andReturn();


        $response = $userSettingController->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testInvalidOldPassword()
    {
        $responseHelper = $this->mock(ResponseHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userRepository = $this->mock(UserRepository::class);
        $userSettingRepository = $this->mock(UserSettingRepository::class);
        $timeZoneRepository = $this->mock(TimezoneRepository::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $payload = [
            'old_password' => 'test123',
            'password' => '12345test',
            'confirm_password' => '12345test',
            'language_id' => 1,
            'timezone_id' => 1,
            'currency' => 'INR',
            'auth' => (object)[
                'password' => Hash::make('test12345')
            ]
        ];
        $request = new Request($payload);
        $tenant = [
            'tenant_id' => 1
        ];

        $userSettingController = new UserSettingController(
            $responseHelper,
            $helpers,
            $userRepository,
            $userSettingRepository,
            $timeZoneRepository,
            $languageHelper,
            $tenantActivatedSettingRepository
        );

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);
        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(false);

        $helpers->shouldReceive('getTenantIdAndSponsorIdFromRequest')
            ->once()
            ->andReturn((object)$tenant);

        $responseHelper->shouldReceive('error')
            ->once()
            ->andReturn();

        $response = $userSettingController->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testSuccessChangesPassword()
    {
        $responseHelper = $this->mock(ResponseHelper::class);
        $helpers = $this->mock(Helpers::class);
        $userRepository = $this->mock(UserRepository::class);
        $userSettingRepository = $this->mock(UserSettingRepository::class);
        $timeZoneRepository = $this->mock(TimezoneRepository::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $jsonReponse = $this->mock(JsonResponse::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);

        $payload = [
            'old_password' => 'test12345',
            'password' => '12345test',
            'confirm_password' => '12345test',
            'language_id' => 1,
            'timezone_id' => 1,
            'currency' => 'INR',
            'auth' => (object)[
                'password' => Hash::make('test12345'),
                'user_id' => 1,
                'email' => 'test@gmail.com'
            ]
        ];
        $request = new Request($payload);
        $tenant = [
            'tenant_id' => 1
        ];

        // should not update user's currency if donation tenant setting is disabled
        $usersData = [
            'language_id' => 1,
            'timezone_id' => 1,
        ];

        $userSettingController = new UserSettingController(
            $responseHelper,
            $helpers,
            $userRepository,
            $userSettingRepository,
            $timeZoneRepository,
            $languageHelper,
            $tenantActivatedSettingRepository
        );

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $helpers->shouldReceive('getTenantIdAndSponsorIdFromRequest')
            ->once()
            ->andReturn((object)$tenant);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->andReturn(false);

        $userRepository->shouldReceive('changePassword')
            ->once()
            ->andReturn(true);

        $helpers->shouldReceive('getSubDomainFromRequest')
            ->once()
            ->andReturn();

        $helpers->shouldReceive('getJwtToken')
            ->once()
            ->andReturn();

        $this->expectsEvents(\App\Events\User\UserActivityLogEvent::class);

        $userSettingRepository->shouldReceive('saveUserData')
            ->once()
            ->with($request->auth->user_id, $usersData)
            ->andReturn();

        $responseHelper->shouldReceive('success')
            ->once()
            ->andReturn(new JsonResponse());

        $response = $userSettingController->store($request);

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
