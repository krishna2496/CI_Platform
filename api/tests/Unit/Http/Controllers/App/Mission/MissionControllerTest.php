<?php

namespace Tests\Unit\Http\Controllers\App\Mission;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\App\Mission\MissionController;
use App\Models\City;
use App\Models\Mission;
use App\Models\UserFilter;
use App\Repositories\City\CityRepository;
use App\Repositories\Country\CountryRepository;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\MissionTheme\MissionThemeRepository;
use App\Repositories\Skill\SkillRepository;
use App\Repositories\State\StateRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Repositories\UnitedNationSDG\UnitedNationSDGRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\UserFilter\UserFilterRepository;
use App\Services\Donation\DonationService;
use App\Transformations\MissionTransformable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as ArrayCollection;
use Mockery;
use Ramsey\Uuid\Uuid;
use TestCase;

class MissionControllerTest extends TestCase
{
    private $missionRepository;
    private $responseHelper;
    private $userFilterRepository;
    private $languageHelper;
    private $helpers;
    private $themeRepository;
    private $skillRepository;
    private $countryRepository;
    private $cityRepository;
    private $userRepository;
    private $stateRepository;
    private $tenantActivatedSettingRepository;
    private $unitedNationSDGRepository;
    private $missionController;

    public function setUp(): void
    {
        parent::setUp();

        $this->missionRepository = $this->mock(MissionRepository::class);
        $this->responseHelper = $this->mock(ResponseHelper::class);
        $this->userFilterRepository = $this->mock(UserFilterRepository::class);
        $this->languageHelper = $this->mock(LanguageHelper::class);
        $this->helpers = $this->mock(Helpers::class);
        $this->themeRepository = $this->mock(MissionThemeRepository::class);
        $this->skillRepository = $this->mock(SkillRepository::class);
        $this->countryRepository = $this->mock(CountryRepository::class);
        $this->cityRepository = $this->mock(CityRepository::class);
        $this->userRepository = $this->mock(UserRepository::class);
        $this->stateRepository = $this->mock(StateRepository::class);
        $this->tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $this->unitedNationSDGRepository = $this->mock(UnitedNationSDGRepository::class);

        $this->missionController = Mockery::mock(MissionController::class.'[transformMission]', [
            $this->missionRepository,
            $this->responseHelper,
            $this->userFilterRepository,
            $this->languageHelper,
            $this->helpers,
            $this->themeRepository,
            $this->skillRepository,
            $this->countryRepository,
            $this->cityRepository,
            $this->userRepository,
            $this->stateRepository,
            $this->tenantActivatedSettingRepository,
            $this->unitedNationSDGRepository
        ]);
    }

    public function testGetMissionList()
    {
        $language = new ArrayCollection([
            [
                'language_id' => 1,
                'code' => 'en',
                'status' => '1'
            ]
        ]);

        $request = new Request([
            'auth' => (object) [
                'user_id' => 1
            ],
            'with_donation_statistics' => true
        ]);

        $this->languageHelper
            ->shouldReceive('getLanguageDetails')
            ->twice()
            ->with($request)
            ->andReturn((object) $language->first());

        $this->languageHelper
            ->shouldReceive('getLanguages')
            ->once()
            ->andReturn($language);

        $userFilters = new UserFilter();
        $userFilters->setAttribute('filters', []);

        $this->userFilterRepository
            ->shouldReceive('saveFilter')
            ->once()
            ->with($request)
            ->andReturn($userFilters);

        $this->userFilterRepository
            ->shouldReceive('userFilter')
            ->once()
            ->with($request)
            ->andReturn($userFilters);

        $missionData = $this->missionData();
        $paginator = $this->getPaginator(
            $missionData,
            count($missionData),
            9
        );

        $this->missionRepository
            ->shouldReceive('getMissions')
            ->once()
            ->with($request, null)
            ->andReturn($paginator);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($request)
            ->andReturn((object) $language->first());

        $this->userRepository
            ->shouldReceive('getUserTimezone')
            ->once()
            ->with($request->auth->user_id)
            ->andReturn('Asia/Manila');

        $this->missionRepository
            ->shouldReceive('getDonationStatistics')
            ->once()
            ->with([
                $missionData->first()->mission_id
            ])
            ->andReturn($missionData);

        $this->missionController
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('transformMission')
            ->once()
            ->andReturn($missionData->first());

        $missionObject = $missionData->map(function ($mission) {
            $mission->setAttribute('donation_statistics', [
                'count' => null,
                'donors' => null,
                'total' => null
            ]);
            return $mission->toArray();
        })->toArray();

        $this->responseHelper
            ->shouldReceive('successWithPagination')
            ->once()
            ->andReturn(new JsonResponse());

        $response = $this->missionController->getMissionList($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testGetMissionDetail()
    {
        $language = new ArrayCollection([
            [
                'language_id' => 1,
                'code' => 'en',
                'status' => '1'
            ]
        ]);

        $request = new Request([
            'auth' => (object) [
                'user_id' => 1
            ],
            'with_donation_statistics' => true
        ]);

        $this->languageHelper
            ->shouldReceive('getLanguageDetails')
            ->once()
            ->with($request)
            ->andReturn((object) $language->first());

        $this->languageHelper
            ->shouldReceive('getLanguages')
            ->once()
            ->andReturn($language);

        $missionData = $this->missionData();
        $this->missionRepository
            ->shouldReceive('getMissionDetail')
            ->once()
            ->with($request, $missionData->first()->mission_id)
            ->andReturn($missionData);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($request)
            ->andReturn([
                'donation'
            ]);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($request)
            ->andReturn((object) $language->first());

        $this->userRepository
            ->shouldReceive('getUserTimezone')
            ->once()
            ->with($request->auth->user_id)
            ->andReturn('Asia/Manila');

        $this->missionRepository
            ->shouldReceive('getDonationStatistics')
            ->once()
            ->with([
                $missionData->first()->mission_id
            ])
            ->andReturn($missionData);

        $this->missionController
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('transformMission')
            ->once()
            ->andReturn($missionData->first());

        $missionObject = $missionData->map(function ($mission) {
            $mission->setAttribute('donation_statistics', [
                'count' => null,
                'donors' => null,
                'total' => null
            ]);
            return $mission;
        })->all();

        $apiData = $missionObject;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_FOUND');

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, $apiData, false)
            ->andReturn(new JsonResponse());

        $response = $this->missionController->getMissionDetail(
            $request,
            $missionData->first()->mission_id
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testGetMissionDetailNoPermission()
    {
        $language = new ArrayCollection([
            [
                'language_id' => 1,
                'code' => 'en',
                'status' => '1'
            ]
        ]);

        $request = new Request([
            'auth' => (object) [
                'user_id' => 1
            ],
            'with_donation_statistics' => true
        ]);

        $this->languageHelper
            ->shouldReceive('getLanguageDetails')
            ->once()
            ->with($request)
            ->andReturn((object) $language->first());

        $this->languageHelper
            ->shouldReceive('getLanguages')
            ->once()
            ->andReturn($language);

        $missionData = $this->missionData();
        $this->missionRepository
            ->shouldReceive('getMissionDetail')
            ->once()
            ->with($request, $missionData->first()->mission_id)
            ->andReturn($missionData);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($request)
            ->andReturn([]);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->never();

        $this->userRepository
            ->shouldReceive('getUserTimezone')
            ->never();

        $this->missionRepository
            ->shouldReceive('getDonationStatistics')
            ->never();

        $this->missionController
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('transformMission')
            ->never();

        $this->responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_TENANT_SETTING_DISABLED'),
                trans('messages.custom_error_message.ERROR_TENANT_SETTING_DISABLED')
            )
            ->andReturn(new JsonResponse());

        $response = $this->missionController->getMissionDetail(
            $request,
            $missionData->first()->mission_id
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testGetMissionDetailException()
    {
        $language = new ArrayCollection([
            [
                'language_id' => 1,
                'code' => 'en',
                'status' => '1'
            ]
        ]);

        $request = new Request([
            'auth' => (object) [
                'user_id' => 1
            ],
            'with_donation_statistics' => true
        ]);

        $this->languageHelper
            ->shouldReceive('getLanguageDetails')
            ->once()
            ->with($request)
            ->andReturn((object) $language->first());

        $this->languageHelper
            ->shouldReceive('getLanguages')
            ->once()
            ->andReturn($language);

        $missionData = $this->missionData();
        $this->missionRepository
            ->shouldReceive('getMissionDetail')
            ->once()
            ->with($request, $missionData->first()->mission_id)
            ->andThrow(new ModelNotFoundException);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->never();

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->never();

        $this->userRepository
            ->shouldReceive('getUserTimezone')
            ->never();

        $this->missionRepository
            ->shouldReceive('getDonationStatistics')
            ->never();

        $this->missionController
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('transformMission');

        $this->responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_NOT_FOUND')
            )
            ->andReturn(new JsonResponse());

        $response = $this->missionController->getMissionDetail(
            $request,
            $missionData->first()->mission_id
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Returns sample of custom fields
     *
     * @return array
     */
    private function missionData()
    {
        $mission = new Mission();
        $mission->setAttribute('mission_id', 1);
        $mission->setAttribute('mission_type', 'DONATION');
        $mission->setAttribute('donationAttribute', true);

        return new Collection([
            $mission
        ]);
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

    /**
     * Close mockery
     *
     * @return Mockery
     */
    public function tearDown(): void
    {
        Mockery::close();
    }
}
