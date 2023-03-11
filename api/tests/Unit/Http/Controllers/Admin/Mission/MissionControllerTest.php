<?php

namespace Tests\Unit\Http\Controllers\Admin\Mission;

use App\Events\User\UserActivityLogEvent;
use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\Mission\MissionController;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedAccount;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\Stripe\StripePaymentGateway;
use App\Models\City;
use App\Models\FavouriteMission;
use App\Models\Mission;
use App\Models\MissionApplication;
use App\Models\MissionDocument;
use App\Models\MissionLanguage;
use App\Models\MissionRating;
use App\Models\MissionSkill;
use App\Models\MissionTab;
use App\Models\MissionTabLanguage;
use App\Models\NotificationType;
use App\Models\Organization;
use App\Models\PaymentGateway\PaymentGatewayAccount;
use App\Models\TimeMission;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\MissionMedia\MissionMediaRepository;
use App\Repositories\Notification\NotificationRepository;
use App\Repositories\Organization\OrganizationRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Services\Donation\DonationService;
use App\Services\Mission\ModelsService;
use App\Services\PaymentGateway\AccountService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Ramsey\Uuid\Uuid;
use TestCase;
use Validator;

class MissionControllerTest extends TestCase
{
    private $paymentGatewayFactory;
    private $accountService;
    private $missionController;
    private $missionRepository;
    private $responseHelper;
    private $request;
    private $languageHelper;
    private $missionMediaRepository;
    private $tenantActivatedSettingRepository;
    private $notificationRepository;
    private $modelService;
    private $organizationRepository;
    private $helpers;
    private $stripePaymentGateway;
    private $donationService;

    public function setUp(): void
    {
        parent::setUp();

        $this->missionRepository = $this->mock(MissionRepository::class);
        $this->responseHelper = $this->mock(ResponseHelper::class);
        $this->request = new Request();
        $this->languageHelper = $this->mock(LanguageHelper::class);
        $this->missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $this->tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $this->notificationRepository = $this->mock(NotificationRepository::class);
        $this->modelService = $this->mock(ModelsService::class);
        $this->organizationRepository = $this->mock(OrganizationRepository::class);
        $this->helpers = $this->mock(Helpers::class);
        $this->accountService = $this->mock(AccountService::class);
        $this->paymentGatewayFactory = $this->mock(PaymentGatewayFactory::class);
        $this->stripePaymentGateway = $this->mock(StripePaymentGateway::class);
        $this->donationService = $this->mock(DonationService::class);

        $this->missionController = new MissionController(
            $this->missionRepository,
            $this->responseHelper,
            $this->request,
            $this->languageHelper,
            $this->missionMediaRepository,
            $this->tenantActivatedSettingRepository,
            $this->notificationRepository,
            $this->organizationRepository,
            $this->modelService,
            $this->helpers,
            $this->accountService,
            $this->paymentGatewayFactory,
            $this->donationService
        );
    }

    /**
     * @testdox Test udpate mission with impact donation attribute with success status
     */
    public function testUpdateImpactDonationAttributeSuccess()
    {
        $data = [
            'impact_donation' => [
                [
                    'impact_donation_id' => str_random(36),
                    'amount' => rand(100000, 200000),
                    'translations' => [
                        [
                            'language_code' => 'es',
                            'content' => str_random(160)
                        ]
                    ]
                ],
                [
                    'amount' => rand(100000, 200000),
                    'translations' => [
                        [
                            'language_code' => 'ab',
                            'content' => str_random(160)
                        ]
                    ]
                ]
            ],
            'publication_status' => 'DRAFT'
        ];

        $requestData = new Request($data);
        $missionId = rand(50000, 70000);
        $missionModel = new Mission();
        $missionModel->mission_type = 'TIME';

        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = $this->mock(Request::class);
        $mission = $this->mock(Mission::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $missionController = $this->mock(MissionController::class);

        $this->expectsEvents(UserActivityLogEvent::class);

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $key = str_random(16);
        $requestHeader = $request->shouldReceive('header')
            ->once()
            ->with('php-auth-user')
            ->andReturn($key);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $missionRepository->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn(
                [
                    config('constants.tenant_settings.VOLUNTEERING'),
                    config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION'),
                ]
            );

        $languageHelper->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($requestData)
            ->andReturn($defaultLanguage);

        $missionModel = new Mission();
        $missionModel->publication_status = 'DRAFT';
        $missionRepository->shouldReceive('getMissionDetailsFromId')
            ->once()
            ->with($missionId, $defaultLanguage->language_id)
            ->andReturn($missionModel);

        $missionRepository->shouldReceive('isMissionDonationImpactLinkedToMission')
            ->once()
            ->with($missionId, $data['impact_donation'][0]['impact_donation_id'])
            ->andReturn();

        $missionRepository->shouldReceive('update')
            ->once()
            ->andReturn();

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_UPDATED');

        $methodResponse = [
            'status' => $apiStatus,
            'message' => $apiMessage
        ];

        $jsonResponse = $this->getJson($methodResponse);

        $responseHelper->shouldReceive('success')
        ->once()
        ->with($apiStatus, $apiMessage)
        ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
    * @testdox Test remove mission tab by mission_tab_id successfully
    *
    * @return void
    */
    public function testRemoveMissionTabByMissionTabIdSuccess()
    {
        $missionTabId = Uuid::uuid4()->toString();

        $methodResponse = [
            'status'=> Response::HTTP_NO_CONTENT,
            'message'=> trans('messages.success.MESSAGE_MISSION_TAB_DELETED')
        ];

        $jsonResponse = new JsonResponse(
            $methodResponse
        );

        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = new Request();
        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);

        $this->expectsEvents(UserActivityLogEvent::class);

        $missionRepository->shouldReceive('deleteMissionTabByMissionTabId')
            ->once()
            ->andReturn(true);

        $responseHelper->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_NO_CONTENT,
                trans('messages.success.MESSAGE_MISSION_TAB_DELETED')
            )
           ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->removeMissionTab($missionTabId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
    * @testdox Test remove mission tab by mission_tab_id error for mission_tab_id does not found
    *
    * @return void
    */
    public function testRemoveMissionTabByMissionTabIdError()
    {
        $missionTabId = Uuid::uuid4()->toString();

        $methodResponse = [
            'errors'=> [
                [
                    'status'=> Response::HTTP_NOT_FOUND,
                    'type'=> Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'code'=> config('constants.error_codes.MISSION_TAB_NOT_FOUND'),
                    'message'=> trans('messages.custom_error_message.MISSION_TAB_NOT_FOUND')
                ]
            ]
        ];

        $jsonResponse = new JsonResponse(
            $methodResponse
        );

        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = new Request();
        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $modelService = $this->mock(ModelsService::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);

        $missionRepository->shouldReceive('deleteMissionTabByMissionTabId')
            ->once()
            ->with($missionTabId)
            ->andThrow($modelNotFoundException);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.MISSION_TAB_NOT_FOUND'),
                trans('messages.custom_error_message.MISSION_TAB_NOT_FOUND')
            )
           ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->removeMissionTab($missionTabId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
     * @testdox Test udpate mission with impact
     */
    public function testUpdateMissionImpactSuccess()
    {
        $this->expectsEvents(UserActivityLogEvent::class);

        $data = [
            'impact' => [
                [
                    'mission_impact_id' => str_random(36),
                    'icon_path' => str_random(100),
                    'sort_key' => rand(100, 200),
                    'translations' => [
                        [
                            'language_code' => 'es',
                            'content' => str_random(160)
                        ]
                    ]
                ],
                [
                    'sort_key' => rand(100, 200),
                    'translations' => [
                        [
                            'language_code' => 'ab',
                            'content' => str_random(160)
                        ]
                    ]
                ]
            ],
            'publication_status' => 'DRAFT'
        ];

        $requestData = new Request($data);
        $missionId = rand(50000, 70000);

        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = $this->mock(Request::class);
        $mission = $this->mock(Mission::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $modelService = $this->mock(ModelsService::class);

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $key = str_random(16);
        $requestHeader = $request->shouldReceive('header')
            ->once()
            ->with('php-auth-user')
            ->andReturn($key);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $missionRepository->shouldReceive('checkExistImpactSortKey')
            ->once()
            ->with($missionId, $requestData->impact)
            ->andReturn(true);

        $missionModel = new Mission();
        $missionModel->publication_status = 'DRAFT';
        $missionModel->mission_type = config('constants.mission_type.GOAL');
        $missionRepository->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $languageHelper->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($requestData)
            ->andReturn($defaultLanguage);

        $missionRepository->shouldReceive('getMissionDetailsFromId')
            ->once()
            ->with($missionId, $defaultLanguage->language_id)
            ->andReturn($missionModel);

        $missionRepository->shouldReceive('isMissionImpactLinkedToMission')
            ->once()
            ->with($missionId, $data['impact'][0]['mission_impact_id'])
            ->andReturn();

        $missionRepository->shouldReceive('update')
            ->once()
            ->andReturn();

        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION')
            ]);

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_UPDATED');

        $methodResponse = [
            'status' => $apiStatus,
            'message' => $apiMessage
        ];

        $jsonResponse = $this->getJson($methodResponse);

        $responseHelper->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage)
            ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
     * @testdox Test update mission with invalid mission_impact_id
     */
    public function testMissionImpactNotFoundError()
    {
        $data = [
            'impact' => [
                [
                    'mission_impact_id' => str_random(36),
                    'icon_path' => str_random(100),
                    'sort_key' => rand(100, 200),
                    'translations' => [
                        [
                            'language_code' => 'es',
                            'content' => str_random(160)
                        ]
                    ]
                ]
            ],
            'publication_status' => 'DRAFT'
        ];

        $requestData = new Request($data);
        $missionId = rand(50000, 70000);

        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = $this->mock(Request::class);
        $mission = $this->mock(Mission::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $modelService = $this->mock(ModelsService::class);

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $key = str_random(16);
        $requestHeader = $request->shouldReceive('header')
            ->once()
            ->with('php-auth-user')
            ->andReturn($key);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $missionRepository->shouldReceive('checkExistImpactSortKey')
            ->once()
            ->with($missionId, $requestData->impact)
            ->andReturn(true);

        $missionModel = new Mission();
        $missionModel->publication_status = 'DRAFT';
        $missionModel->mission_type = config('constants.mission_type.TIME');
        $missionRepository->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $languageHelper->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($requestData)
            ->andReturn($defaultLanguage);

        $missionRepository->shouldReceive('getMissionDetailsFromId')
            ->once()
            ->with($missionId, $defaultLanguage->language_id)
            ->andReturn($missionModel);

        $missionRepository->shouldReceive('isMissionImpactLinkedToMission')
            ->once()
            ->with($missionId, $data['impact'][0]['mission_impact_id'])
            ->andThrow($modelNotFoundException);

        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION')
            ]);

        $methodResponse = [
            'errors'=> [
                [
                    'status'=> Response::HTTP_NOT_FOUND,
                    'type'=> Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'code'=>  config('constants.error_codes.IMPACT_MISSION_NOT_FOUND'),
                    'message'=> trans('messages.custom_error_message.ERROR_IMPACT_MISSION_NOT_FOUND')
                ]
            ]
        ];

        $jsonResponse = $this->getJson($methodResponse);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.IMPACT_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_IMPACT_MISSION_NOT_FOUND')
            )
            ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
    * @testdox Test delete mission impact
    *
    * @return void
    */
    public function testDeleteMissionImpactSuccess()
    {
        $missionImpactId = Uuid::uuid4()->toString();

        $methodResponse = [
            'status'=> Response::HTTP_NO_CONTENT,
            'message'=> trans('messages.success.MESSAGE_MISSION_IMPACT_DELETED')
        ];

        $jsonResponse = new JsonResponse(
            $methodResponse
        );

        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = new Request();
        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);

        $this->expectsEvents(UserActivityLogEvent::class);

        $missionRepository->shouldReceive('deleteMissionImpact')
            ->once()
            ->andReturn(true);

        $responseHelper->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_NO_CONTENT,
                trans('messages.success.MESSAGE_MISSION_IMPACT_DELETED')
            )
           ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->removeMissionImpact($missionImpactId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
    * @testdox Test delete mission impact with invalid ID
    *
    * @return void
    */
    public function testDeleteMissionImpactError()
    {
        $missionTabId = Uuid::uuid4()->toString();

        $methodResponse = [
            'errors'=> [
                [
                    'status'=> Response::HTTP_NOT_FOUND,
                    'type'=> Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'code'=> config('constants.error_codes.IMPACT_MISSION_NOT_FOUND'),
                    'message'=> trans('messages.custom_error_message.ERROR_IMPACT_MISSION_NOT_FOUND')
                ]
            ]
        ];

        $jsonResponse = new JsonResponse(
            $methodResponse
        );

        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = new Request();
        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $modelService = $this->mock(ModelsService::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);

        $missionRepository->shouldReceive('deleteMissionImpact')
            ->once()
            ->with($missionTabId)
            ->andThrow($modelNotFoundException);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.IMPACT_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_IMPACT_MISSION_NOT_FOUND')
            )
           ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->removeMissionImpact($missionTabId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    public function testMissionStoreValidationFailure()
    {
        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = new Request();
        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $modelService = $this->mock(ModelsService::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $requestData = new Request();

        $jsonResponse = new JsonResponse();

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_MISSION_DATA'),
                'The mission type field is required.'
            )
           ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );
        $response = $callController->store($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testMissionStoreOrganizationNameRequired()
    {
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'legal_number' => 1,
                'phone_number' => 123,
                'address_line_1' => 'test',
                'address_line_2' => '2323',
                'city_id' => '',
                'country_id' => '',
                'postal_code' => 1
            ],
            'organisation_detail' => [
                [
                    'lang' => 'en',
                    'detail' => 'test oraganization detail3333333333'
                ]
            ],
            'location' => [
                'city_id' => '1',
                'country_code' => 'US'
            ],
            'mission_detail' => [
                [
                    'lang' => 'en',
                    'title' => 'testing api mission details',
                    'short_description' => 'this is testing api with all mission details',
                    'objective' => 'To test and check',
                    'label_goal_achieved' => 'test percentage',
                    'label_goal_objective' => 'check test percentage',
                    'section' => [
                        [
                            'title' => 'string',
                            'description' => 'string'
                        ]
                    ],
                    'custom_information' => [
                        [
                            'title' => 'string',
                            'description' => 'string'
                        ]
                    ]
                ]
            ],
            'impact' => [
                [
                    'icon_path' => 'filepath available',
                    'sort_key' => 1525,
                    'translations' => [
                        [
                            'language_code' => 'tr',
                            'content' => 'mission impact content other lang.'
                        ],
                        [
                            'language_code' => 'es',
                            'content' => 'mission impact content es lang.'
                        ]
                    ]
                ],
                [
                    'sort_key' => 2,
                    'translations' => [
                        [
                            'language_code' => 'fr',
                            'content' => 'mission impact content fr lang.'
                        ]
                    ]
                ]
            ],
            'skills' => [
                [
                    'skill_id' => 2
                ]
            ],
            'volunteering_attribute' =>
            [
                'availability_id' => 1,
                'total_seats' => 25,
                'is_virtual' => 1
            ],
            'start_date' => '2020-05-13T06 =>07 =>47.115Z',
            'end_date' => '2020-05-21T06 =>07 =>47.115Z',
            'mission_type' => config('constants.mission_type.GOAL'),
            'goal_objective' => '535',
            'application_deadline' => '2020-05-16T06 =>07 =>47.115Z',
            'application_start_date' => '2020-05-18T06 =>07 =>47.115Z',
            'application_start_time' => '2020-05-18T06 =>07 =>47.115Z',
            'application_end_date' => '2020-05-20T06 =>07 =>47.115Z',
            'application_end_time' => '2020-05-20T06 =>07 =>47.115Z',
            'publication_status' => 'APPROVED',
            'availability_id' => 1,
            'is_virtual' => '0',
            'un_sdg' => [1, 2, 3]
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = new Request();
        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $modelService = $this->mock(ModelsService::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $requestData = new Request($input);

        $jsonResponse = new JsonResponse();

        $organizationRepository->shouldReceive('find')
            ->once()
            ->andReturn(false);

        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION')
            ]);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_MISSION_DATA'),
                trans('messages.custom_error_message.ERROR_ORGANIZATION_NAME_REQUIRED')
            )
           ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );
        $response = $callController->store($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Test not found mission with impact donation attribute with error status
     */
    public function testImpactDonationMissionNotLinkWithMissionError()
    {
        $data = [
            'impact_donation' => [
                [
                    'impact_donation_id' => str_random(36),
                    'amount' => rand(100000, 200000),
                    'translations' => [
                        [
                            'language_code' => 'es',
                            'content' => str_random(160)
                        ]
                    ]
                ]
            ],
            'publication_status' => 'DRAFT'
        ];

        $requestData = new Request($data);
        $missionId = rand(50000, 70000);
        $missionModel = new Mission();
        $missionModel->mission_type = 'TIME';

        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = $this->mock(Request::class);
        $mission = $this->mock(Mission::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $modelsService = $this->mock(ModelsService::class);

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $key = str_random(16);
        $requestHeader = $request->shouldReceive('header')
            ->once()
            ->with('php-auth-user')
            ->andReturn($key);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $missionRepository->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn(
                [
                    config('constants.tenant_settings.VOLUNTEERING'),
                    config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION'),
                ]
            );

        $languageHelper->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($requestData)
            ->andReturn($defaultLanguage);

        $missionModel = new Mission();
        $missionModel->publication_status = 'DRAFT';
        $missionRepository->shouldReceive('getMissionDetailsFromId')
            ->once()
            ->with($missionId, $defaultLanguage->language_id)
            ->andReturn($missionModel);

        $missionRepository->shouldReceive('isMissionDonationImpactLinkedToMission')
            ->once()
            ->with($missionId, $data['impact_donation'][0]['impact_donation_id'])
            ->andThrow($modelNotFoundException);

        $methodResponse = [
            'errors' => [
                [
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'code' =>  config('constants.error_codes.IMPACT_DONATION_MISSION_NOT_FOUND'),
                    'message' => trans('messages.custom_error_message.ERROR_IMPACT_DONATION_MISSION_NOT_FOUND')
                ]
            ]
        ];

        $jsonResponse = $this->getJson($methodResponse);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.IMPACT_DONATION_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_IMPACT_DONATION_MISSION_NOT_FOUND')
            )
            ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelsService
        );

        $response = $callController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    public function testMissionStoreSuccess()
    {
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
                'legal_number' =>1,
                'phone_number' =>123,
                'address_line_1' =>'test',
                'address_line_2' =>'2323',
                'city_id' =>'',
                'country_id' =>'',
                'postal_code' =>1
            ],
            'organisation_detail' => [
                [
                'lang' => 'en',
                'detail' => 'test oraganization detail3333333333'
                ]
            ],
            'location' => [
                'city_id' => '1',
                'country_code' => 'US'
            ],
            'mission_detail' => [
                [
                    'lang' => 'en',
                    'title' => 'testing api mission details',
                    'short_description' => 'this is testing api with all mission details',
                    'objective' => 'To test and check',
                    'label_goal_achieved' => 'test percentage',
                    'label_goal_objective' => 'check test percentage',
                    'section' => [
                        [
                            'title' => 'string',
                            'description' => 'string'
                        ]
                    ],
                    'custom_information' => [
                        [
                            'title' => 'string',
                            'description' => 'string'
                        ]
                    ]
                ]
            ],
            'impact' => [
                [
                    'icon_path' => 'filepath available',
                    'sort_key' => 1525,
                    'translations' => [
                        [
                            'language_code' => 'tr',
                            'content' => 'mission impact content other lang.'
                        ],
                        [
                            'language_code' => 'es',
                            'content' => 'mission impact content es lang.'
                        ]
                    ]
                ],
                [
                    'sort_key' => 2,
                    'translations' => [
                        [
                            'language_code' => 'fr',
                            'content' => 'mission impact content fr lang.'
                        ]
                    ]
                ]
            ],
            'skills' => [
                [
                    'skill_id' => 2
                ]
            ],
            'volunteering_attribute' => [
                'availability_id' => 1,
                'total_seats' => 25,
                'is_virtual' => 1
            ],
            'start_date' => '2020-05-13T06 =>07 =>47.115Z',
            'end_date' => '2020-05-21T06 =>07 =>47.115Z',
            'mission_type' => config('constants.mission_type.GOAL'),
            'goal_objective' => '535',
            'application_deadline' => '2020-05-16T06 =>07 =>47.115Z',
            'application_start_date' => '2020-05-18T06 =>07 =>47.115Z',
            'application_start_time' => '2020-05-18T06 =>07 =>47.115Z',
            'application_end_date' => '2020-05-20T06 =>07 =>47.115Z',
            'application_end_time' => '2020-05-20T06 =>07 =>47.115Z',
            'publication_status' => 'APPROVED',
            'availability_id' => 1,
            'is_virtual' => false,
            'un_sdg' => [1, 2, 3]
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = new Request();
        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $modelService = $this->mock(ModelsService::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = rand();

        $jsonResponse = new JsonResponse();

        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION')
            ]);

        $organizationRepository->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);
        $missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $missionRepository->shouldReceive('store')
            ->once()
            ->andReturn($missionModel);

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_ADDED');
        $apiData = ['mission_id' => $missionModel->mission_id];

        $responseHelper->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, $apiData)
            ->andReturn($jsonResponse);

        $this->expectsEvents(UserActivityLogEvent::class);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );
        $response = $callController->store($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Test store method validation error
     */
    public function testStoreValidationError()
    {
        $organizationId = Uuid::uuid4()->toString();
        $data = [
            'organization' => [
                'organization_id' => $organizationId
            ],
            'location' => [
                'city_id' => 1,
                'country_code' => 'PH'
            ],
            'theme_id' => 'abc',
            'publication_status' => true,
            'availability_id' => 1,
            'mission_type' => config('constants.mission_type.GOAL'),
            'mission_detail' => [],
            'documents' => [
                [
                    'sort_order' => 0,
                    'document_path' => 'http://admin-m7pww5ymmj28.back.staging.optimy.net/assets/images/optimy-logo.png'
                ]
            ],
            'volunteering_attribute' => [
                'total_seats' => 100,
                'availability_id' => 1,
                'is_virtual' => 1
            ]
        ];

        $requestData = new Request($data);
        $missionId = rand(50000, 70000);

        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = $this->mock(Request::class);
        $mission = $this->mock(Mission::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $responseHelper = $this->mock(ResponseHelper::class);

        $key = str_random(16);
        $requestHeader = $request->shouldReceive('header')
        ->once()
        ->with('php-auth-user')
        ->andReturn($key);

        $errors = new Collection([
            config('constants.error_codes.ERROR_INVALID_MISSION_DATA')
        ]);
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_MISSION_DATA'),
                $errors->first()
            );

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->store($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test sort key of mission_tab update is already exist error
    *
    * @return void
    */
    public function testMissionTabSortKeyExistError()
    {
        $missionTabId = Uuid::uuid4()->toString();
        $data = [
            'mission_tabs' => [
                [
                    'mission_tab_id' => $missionTabId,
                    'sort_key' => rand(100, 200),
                    'translations' => [
                        [
                            'lang' => 'es',
                            'name' => str_random(160),
                            'sections' => [
                                [
                                    'title'=> str_random(20),
                                    'content' => str_random(200)
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $requestData = new Request($data);
        $missionId = rand(50000, 70000);

        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = $this->mock(Request::class);
        $mission = $this->mock(Mission::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $modelService = $this->mock(ModelsService::class);

        $key = str_random(16);
        $requestHeader = $request->shouldReceive('header')
            ->once()
            ->with('php-auth-user')
            ->andReturn($key);

        $missionModel = new Mission();
        $missionModel->mission_type = config('constants.mission_type.GOAL');
        $missionRepository->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $missionRepository->shouldReceive('checkExistTabSortKey')
            ->once()
            ->with($missionId, $requestData->mission_tabs)
            ->andReturn(false);

        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION')
            ]);

        $methodResponse = [
            'errors'=> [
                [
                    'status'=> Response::HTTP_UNPROCESSABLE_ENTITY,
                    'type'=> Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    'code'=>  config('constants.error_codes.ERROR_SORT_KEY_ALREADY_EXIST'),
                    'message'=> trans('messages.custom_error_message.ERROR_SORT_KEY_ALREADY_EXIST')
                ]
            ]
        ];

        $jsonResponse = $this->getJson($methodResponse);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_SORT_KEY_ALREADY_EXIST'),
                trans('messages.custom_error_message.ERROR_SORT_KEY_ALREADY_EXIST')
            )
            ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
    * @testdox Test sort key of mission_impact update is already exist error
    *
    * @return void
    */
    public function testMissionImpactSortKeyExistError()
    {
        $missionImpactId = Uuid::uuid4()->toString();
        $data = [
            'impact' => [
                [
                    'mission_impact_id' => $missionImpactId,
                    'icon_path' => 'https://cdn.pixabay.com/photo/2020/09/13/16/10/rose-beetle-5568669_960_720.jpg',
                    'sort_key' => rand(100, 200),
                    'translations' => [
                        [
                            'language_code' => 'es',
                            'content' => str_random(160)
                        ]
                    ]
                ]
            ]
        ];

        $requestData = new Request($data);
        $missionId = rand(50000, 70000);

        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = $this->mock(Request::class);
        $mission = $this->mock(Mission::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $modelService = $this->mock(ModelsService::class);

        $key = str_random(16);
        $requestHeader = $request->shouldReceive('header')
            ->once()
            ->with('php-auth-user')
            ->andReturn($key);

        $missionModel = new Mission();
        $missionModel->mission_type = config('constants.mission_type.GOAL');
        $missionRepository->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $missionRepository->shouldReceive('checkExistImpactSortKey')
            ->once()
            ->with($missionId, $requestData->impact)
            ->andReturn(false);

        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION')
            ]);

        $methodResponse = [
            'errors'=> [
                [
                    'status'=> Response::HTTP_UNPROCESSABLE_ENTITY,
                    'type'=> Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    'code'=>  config('constants.error_codes.ERROR_IMPACT_SORT_KEY_ALREADY_EXIST'),
                    'message'=> trans('messages.custom_error_message.ERROR_IMPACT_SORT_KEY_ALREADY_EXIST')
                ]
            ]
        ];

        $jsonResponse = $this->getJson($methodResponse);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_IMPACT_SORT_KEY_ALREADY_EXIST'),
                trans('messages.custom_error_message.ERROR_IMPACT_SORT_KEY_ALREADY_EXIST')
            )
            ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
    * @testdox Test remove mission impact donation by mission_impact_donation_id error for mission_impact_donation_id does not found
    *
    * @return void
    */
    public function testRemoveMissionImpactDonationByMissionImpactDoantionIdError()
    {
        $missionImpactDonationId = Uuid::uuid4()->toString();

        $methodResponse = [
            'errors'=> [
                [
                    'status'=> Response::HTTP_NOT_FOUND,
                    'type'=> Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'code'=> config('constants.error_codes.IMPACT_DONATION_MISSION_NOT_FOUND'),
                    'message'=> trans('messages.custom_error_message.ERROR_IMPACT_DONATION_MISSION_NOT_FOUND')
                ]
            ]
        ];

        $jsonResponse = new JsonResponse(
            $methodResponse
        );

        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = new Request();
        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $modelNotFoundException = $this->mock(ModelNotFoundException::class);
        $modelService = $this->mock(ModelsService::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);
        $helpers = $this->mock(Helpers::class);

        $missionRepository->shouldReceive('deleteMissionImpactDonation')
            ->once()
            ->with($missionImpactDonationId)
            ->andThrow($modelNotFoundException);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.IMPACT_DONATION_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_IMPACT_DONATION_MISSION_NOT_FOUND')
            )
           ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService,
            $helpers
        );

        $response = $callController->removeMissionImpactDonation($missionImpactDonationId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

    /**
    * @testdox Test remove mission impact donation by mission_impact_donation_id successfully
    *
    * @return void
    */
    public function testRemoveMissionImpactDonationByMissionImpactDonationIdSuccess()
    {
        $missionTabId = Uuid::uuid4()->toString();

        $methodResponse = [
            'status'=> Response::HTTP_NO_CONTENT,
            'message'=> trans('messages.success.MESSAGE_MISSION_IMPACT_DONATION_DELETED')
        ];

        $jsonResponse = new JsonResponse(
            $methodResponse
        );

        $missionRepository = $this->mock(MissionRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $request = new Request();
        $languageHelper = $this->mock(LanguageHelper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $notificationRepository = $this->mock(NotificationRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $organizationRepository = $this->mock(OrganizationRepository::class);

        $this->expectsEvents(UserActivityLogEvent::class);

        $missionRepository->shouldReceive('deleteMissionImpactDonation')
            ->once()
            ->andReturn(true);

        $responseHelper->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_NO_CONTENT,
                trans('messages.success.MESSAGE_MISSION_IMPACT_DONATION_DELETED')
            )
           ->andReturn($jsonResponse);

        $callController = $this->getController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService
        );

        $response = $callController->removeMissionImpactDonation($missionTabId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($methodResponse, json_decode($response->getContent(), true));
    }

        public function testStoreOrganizationGatewayAccount()
    {
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
                'legal_number' =>1,
                'phone_number' =>123,
                'address_line_1' =>'test',
                'address_line_2' =>'2323',
                'city_id' =>'',
                'country_id' =>'',
                'postal_code' =>1,
                'payment_gateway_account' => [
                    'payment_gateway' => 'STRIPE',
                    'payment_gateway_account_id' => 'acc_xxxxxxxxxxxx'
                ]
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000',
                'show_goal_amount' => false,
                'show_donation_percentage' => false,
                'show_donation_meter' => false,
                'show_donation_count' => false,
                'show_donors_count' => false,
                'disable_when_funded' => true
            ],
            'location' => [
                'city_id' => '1',
                'country_code' => 'US'
            ],
            'mission_detail' => [
                [
                    'lang' => 'en',
                    'title' => 'testing api mission details',
                    'short_description' => 'this is testing api with all mission details',
                ]
            ],
            'start_date' => '2020-05-13T06 =>07 =>47.115Z',
            'end_date' => '2020-05-21T06 =>07 =>47.115Z',
            'mission_type' => config('constants.mission_type.DONATION'),
            'publication_status' => 'APPROVED'
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = rand();

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with(
                $requestData
            )
            ->andReturn([
                config('constants.tenant_settings.DONATION')
            ]);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);
        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $paymentAccount = new PaymentGatewayDetailedAccount();
        $paymentAccount->setPayoutsEnabled(true);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $paymentGatewayAccount = new PaymentGatewayAccount();
        $paymentGatewayAccount
            ->setAttribute('organization_id', (string) $input['organization']['organization_id'])
            ->setAttribute('payment_gateway_account_id', $input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->setAttribute('payment_gateway', config('constants.payment_gateway_types.'.$input['organization']['payment_gateway_account']['payment_gateway']));

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->once()
            ->with($input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->andReturn($paymentAccount);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->once()
            ->with(config('constants.payment_gateway_types.STRIPE'))
            ->andReturn($this->stripePaymentGateway);

        $this->accountService
            ->shouldReceive('save')
            ->once()
            ->andReturn($paymentGatewayAccount);

        $this->organizationRepository->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $this->missionRepository->shouldReceive('store')
            ->once()
            ->andReturn($missionModel);

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_ADDED');
        $apiData = ['mission_id' => $missionModel->mission_id];

        $this->responseHelper->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, $apiData)
            ->andReturn($jsonResponse);

        $this->expectsEvents(UserActivityLogEvent::class);

        $response = $this->missionController->store($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testStoreOrganizationGatewayAccountPayoutDisabled()
    {
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
                'legal_number' =>1,
                'phone_number' =>123,
                'address_line_1' =>'test',
                'address_line_2' =>'2323',
                'city_id' =>'',
                'country_id' =>'',
                'postal_code' =>1,
                'payment_gateway_account' => [
                    'payment_gateway' => 'STRIPE',
                    'payment_gateway_account_id' => 'acc_xxxxxxxxxxxx'
                ]
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000',
                'show_goal_amount' => false,
                'show_donation_percentage' => false,
                'show_donation_meter' => false,
                'show_donation_count' => true,
                'show_donors_count' => false,
                'disable_when_funded' => true
            ],
            'location' => [
                'city_id' => '1',
                'country_code' => 'US'
            ],
            'mission_detail' => [
                [
                    'lang' => 'en',
                    'title' => 'testing api mission details',
                    'short_description' => 'this is testing api with all mission details'
                ]
            ],
            'start_date' => '2020-05-13T06 =>07 =>47.115Z',
            'end_date' => '2020-05-21T06 =>07 =>47.115Z',
            'mission_type' => config('constants.mission_type.DONATION'),
            'publication_status' => 'APPROVED'
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = rand();

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with(
                $requestData
            )
            ->andReturn([
                config('constants.tenant_settings.DONATION')
            ]);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);
        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $paymentAccount = new PaymentGatewayDetailedAccount();
        $paymentAccount->setPayoutsEnabled(false);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->once()
            ->with($input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->andReturn($paymentAccount);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->once()
            ->with(config('constants.payment_gateway_types.STRIPE'))
            ->andReturn($this->stripePaymentGateway);

        $this->accountService
            ->shouldReceive('save')
            ->never();

        $this->missionRepository
            ->shouldReceive('store')
            ->never();

        // Set response data
        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID'),
                'Account payouts is not enabled'
            );

        $response = $this->missionController->store($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testStoreOrganizationGatewayAccountInvalid()
    {
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
                'legal_number' =>1,
                'phone_number' =>123,
                'address_line_1' =>'test',
                'address_line_2' =>'2323',
                'city_id' =>'',
                'country_id' =>'',
                'postal_code' =>1,
                'payment_gateway_account' => [
                    'payment_gateway' => 'STRIPE',
                    'payment_gateway_account_id' => 'acc_xxxxxxxxxxxx'
                ]
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000',
                'show_goal_amount' => false,
                'show_donation_percentage' => false,
                'show_donation_meter' => false,
                'show_donation_count' => false,
                'show_donors_count' => true,
                'disable_when_funded' => true
            ],
            'location' => [
                'city_id' => '1',
                'country_code' => 'US'
            ],
            'mission_detail' => [
                [
                    'lang' => 'en',
                    'title' => 'testing api mission details',
                    'short_description' => 'this is testing api with all mission details'
                ]
            ],
            'start_date' => '2020-05-13T06 =>07 =>47.115Z',
            'end_date' => '2020-05-21T06 =>07 =>47.115Z',
            'mission_type' => config('constants.mission_type.DONATION'),
            'publication_status' => 'APPROVED'
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = rand();

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with(
                $requestData
            )
            ->andReturn([
                config('constants.tenant_settings.DONATION')
            ]);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);
        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $paymentAccount = new PaymentGatewayDetailedAccount();
        $paymentAccount->setPayoutsEnabled(false);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->once()
            ->with($input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->andThrow(new PaymentGatewayException);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->once()
            ->with(config('constants.payment_gateway_types.STRIPE'))
            ->andReturn($this->stripePaymentGateway);

        $this->accountService
            ->shouldReceive('save')
            ->never();

        $this->missionRepository
            ->shouldReceive('store')
            ->never();

        // Set response data
        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID'),
                'Invalid payment gateway account id'
            );

        $response = $this->missionController->store($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testStoreOrganizationGatewayAccountMissingButNoDb()
    {
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
                'legal_number' =>1,
                'phone_number' =>123,
                'address_line_1' =>'test',
                'address_line_2' =>'2323',
                'city_id' =>'',
                'country_id' =>'',
                'postal_code' =>1
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000',
                'show_goal_amount' => false,
                'show_donation_percentage' => false,
                'show_donation_meter' => false,
                'show_donation_count' => true,
                'show_donors_count' => false,
                'disable_when_funded' => true
            ],
            'location' => [
                'city_id' => '1',
                'country_code' => 'US'
            ],
            'mission_detail' => [
                [
                    'lang' => 'en',
                    'title' => 'testing api mission details',
                    'short_description' => 'this is testing api with all mission details',
                    'objective' => 'To test and check',
                    'label_goal_achieved' => 'test percentage',
                    'label_goal_objective' => 'check test percentage',
                    'section' => [
                        [
                            'title' => 'string',
                            'description' => 'string'
                        ]
                    ],
                    'custom_information' => [
                        [
                            'title' => 'string',
                            'description' => 'string'
                        ]
                    ]
                ]
            ],
            'start_date' => '2020-05-13T06 =>07 =>47.115Z',
            'end_date' => '2020-05-21T06 =>07 =>47.115Z',
            'mission_type' => config('constants.mission_type.TIME'),
            'publication_status' => 'APPROVED'
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = rand();

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with(
                $requestData
            )
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION')
            ]);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);
        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $this->accountService
            ->shouldReceive('getByOrgId')
            ->once()
            ->with($requestData->input('organization.organization_id'))
            ->andReturn(null);

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->never();

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->never();

        $this->accountService
            ->shouldReceive('save')
            ->never();

        $this->missionRepository
            ->shouldReceive('store')
            ->never();

        // Set response data
        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_ORGANIZATION_PAYMENT_GATEWAY_ACCOUNT'),
                'Organization payment_gateway and payment_gateway_account_id is required'
            );

        $response = $this->missionController->store($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testStoreOrganizationGatewayAccountMissingButInDb()
    {
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
                'legal_number' =>1,
                'phone_number' =>123,
                'address_line_1' =>'test',
                'address_line_2' =>'2323',
                'city_id' =>'',
                'country_id' =>'',
                'postal_code' =>1
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000',
                'show_goal_amount' => false,
                'show_donation_percentage' => false,
                'show_donation_meter' => false,
                'show_donation_count' => true,
                'show_donors_count' => false,
                'disable_when_funded' => true
            ],
            'location' => [
                'city_id' => '1',
                'country_code' => 'US'
            ],
            'mission_detail' => [
                [
                    'lang' => 'en',
                    'title' => 'testing api mission details',
                    'short_description' => 'this is testing api with all mission details'
                ]
            ],
            'start_date' => '2020-05-13T06 =>07 =>47.115Z',
            'end_date' => '2020-05-21T06 =>07 =>47.115Z',
            'mission_type' => config('constants.mission_type.TIME'),
            'publication_status' => 'APPROVED'
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = rand();

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION')
            ]);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $paymentGatewayAccount = new PaymentGatewayAccount();

        $this->accountService
            ->shouldReceive('getByOrgId')
            ->once()
            ->with($requestData->input('organization.organization_id'))
            ->andReturn($paymentGatewayAccount);

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->never();

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->never();

        $this->accountService
            ->shouldReceive('save')
            ->never();

        $this->missionRepository->shouldReceive('store')
            ->once()
            ->andReturn($missionModel);

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_ADDED');
        $apiData = ['mission_id' => $missionModel->mission_id];

        $this->responseHelper->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, $apiData)
            ->andReturn($jsonResponse);

        $this->expectsEvents(UserActivityLogEvent::class);

        $response = $this->missionController->store($requestData);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     */
    public function testUpdateOrganizationGatewayAccount()
    {
        $missionId = '1';
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
                'legal_number' =>1,
                'phone_number' =>123,
                'address_line_1' =>'test',
                'address_line_2' =>'2323',
                'city_id' =>'',
                'country_id' =>'',
                'postal_code' =>1,
                'payment_gateway_account' => [
                    'payment_gateway' => 'STRIPE',
                    'payment_gateway_account_id' => 'acc_xxxxxxxxxxxx'
                ]
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000',
                'show_goal_amount' => false,
                'show_donation_percentage' => false,
                'show_donation_meter' => false,
                'show_donation_count' => true,
                'show_donors_count' => false,
                'disable_when_funded' => true
            ],
            'location' => [
                'city_id' => '1',
                'country_code' => 'US'
            ],
            'mission_detail' => [
                [
                    'lang' => 'en',
                    'title' => 'testing api mission details',
                    'short_description' => 'this is testing api with all mission details'
                ]
            ],
            'start_date' => '2020-05-13T06 =>07 =>47.115Z',
            'end_date' => '2020-05-21T06 =>07 =>47.115Z',
            'mission_type' => config('constants.mission_type.TIME'),
            'publication_status' => 'APPROVED'
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = $missionId;

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->twice()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION')
            ]);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($requestData)
            ->andReturn((object) [
                'language_id' => 1,
                'code' => 'en',
                'name' => 'English',
                'default' => '1'
            ]);

        $this->missionRepository
            ->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $this->missionRepository
            ->shouldReceive('getMissionDetailsFromId')
            ->once()
            ->with($missionId, 1)
            ->andReturn($missionModel);

        $paymentAccount = new PaymentGatewayDetailedAccount();
        $paymentAccount->setPayoutsEnabled(true);

        $paymentGatewayAccount = new PaymentGatewayAccount();
        $paymentGatewayAccount
            ->setAttribute('organization_id', (string) $input['organization']['organization_id'])
            ->setAttribute('payment_gateway_account_id', $input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->setAttribute('payment_gateway', config('constants.payment_gateway_types.'.$input['organization']['payment_gateway_account']['payment_gateway']));

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->once()
            ->with($input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->andReturn($paymentAccount);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->once()
            ->with(config('constants.payment_gateway_types.STRIPE'))
            ->andReturn($this->stripePaymentGateway);

        $this->accountService
            ->shouldReceive('save')
            ->once()
            ->andReturn($paymentGatewayAccount);

        $this->missionRepository
            ->shouldReceive('update')
            ->once()
            ->andReturn($missionModel);

        // Set response data
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_UPDATED');

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage)
            ->andReturn($jsonResponse);

        $this->expectsEvents(UserActivityLogEvent::class);

        $externalMock = $this->mock('overload:App\Models\NotificationType');
        $externalMock
            ->shouldReceive('where')
            ->once()
            ->with('notification_type', config('constants.notification_type_keys.NEW_MISSIONS'))
            ->andReturnSelf();

        $externalMock
            ->shouldReceive('first')
            ->once()
            ->andReturn((object) [
                'notification_type_id' => 'type'
            ]);

        $response = $this->missionController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUpdateOrganizationGatewayAccountPayoutDisabled()
    {
        $missionId = '1';
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
                'payment_gateway_account' => [
                    'payment_gateway' => 'STRIPE',
                    'payment_gateway_account_id' => 'acc_xxxxxxxxxxxx'
                ]
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000'
            ]
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = $missionId;

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.DONATION')
            ]);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);
        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($requestData)
            ->andReturn((object) [
                'language_id' => 1,
                'code' => 'en',
                'name' => 'English',
                'default' => '1'
            ]);

        $this->missionRepository
            ->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $this->missionRepository
            ->shouldReceive('getMissionDetailsFromId')
            ->once()
            ->with($missionId, 1)
            ->andReturn($missionModel);

        $paymentAccount = new PaymentGatewayDetailedAccount();
        $paymentAccount->setPayoutsEnabled(false);

        $paymentGatewayAccount = new PaymentGatewayAccount();
        $paymentGatewayAccount
            ->setAttribute('organization_id', (string) $input['organization']['organization_id'])
            ->setAttribute('payment_gateway_account_id', $input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->setAttribute('payment_gateway', config('constants.payment_gateway_types.'.$input['organization']['payment_gateway_account']['payment_gateway']));

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->once()
            ->with($input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->andReturn($paymentAccount);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->once()
            ->with(config('constants.payment_gateway_types.STRIPE'))
            ->andReturn($this->stripePaymentGateway);

        $this->accountService
            ->shouldReceive('save')
            ->never();

        $this->missionRepository
            ->shouldReceive('update')
            ->never();

        // Set response data
        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID'),
                'Account payouts is not enabled'
            );

        $response = $this->missionController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUpdateOrganizationGatewayAccountInvalid()
    {
        $missionId = '1';
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
                'payment_gateway_account' => [
                    'payment_gateway' => 'STRIPE',
                    'payment_gateway_account_id' => 'acc_xxxxxxxxxxxx'
                ]
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000'
            ]
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = $missionId;

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.DONATION')
            ]);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);
        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($requestData)
            ->andReturn((object) [
                'language_id' => 1,
                'code' => 'en',
                'name' => 'English',
                'default' => '1'
            ]);

        $this->missionRepository
            ->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $this->missionRepository
            ->shouldReceive('getMissionDetailsFromId')
            ->once()
            ->with($missionId, 1)
            ->andReturn($missionModel);

        $paymentAccount = new PaymentGatewayDetailedAccount();
        $paymentAccount->setPayoutsEnabled(false);

        $paymentGatewayAccount = new PaymentGatewayAccount();
        $paymentGatewayAccount
            ->setAttribute('organization_id', (string) $input['organization']['organization_id'])
            ->setAttribute('payment_gateway_account_id', $input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->setAttribute('payment_gateway', config('constants.payment_gateway_types.'.$input['organization']['payment_gateway_account']['payment_gateway']));

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->once()
            ->with($input['organization']['payment_gateway_account']['payment_gateway_account_id'])
            ->andThrow(new PaymentGatewayException);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->once()
            ->with(config('constants.payment_gateway_types.STRIPE'))
            ->andReturn($this->stripePaymentGateway);

        $this->accountService
            ->shouldReceive('save')
            ->never();

        $this->missionRepository
            ->shouldReceive('update')
            ->never();

        // Set response data
        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID'),
                'Invalid payment gateway account id'
            );

        $response = $this->missionController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     */
    public function testUpdateOrganizationGatewayAccountMissionButInDb()
    {
        $missionId = '1';
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name',
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000'
            ]
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = $missionId;

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.VOLUNTEERING_MISSION'),
                config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION')
            ]);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);

        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($requestData)
            ->andReturn((object) [
                'language_id' => 1,
                'code' => 'en',
                'name' => 'English',
                'default' => '1'
            ]);

        $this->missionRepository
            ->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $this->missionRepository
            ->shouldReceive('getMissionDetailsFromId')
            ->once()
            ->with($missionId, 1)
            ->andReturn($missionModel);

        $paymentGatewayAccount = new PaymentGatewayAccount();

        $this->accountService
            ->shouldReceive('getByOrgId')
            ->once()
            ->with($requestData->input('organization.organization_id'))
            ->andReturn($paymentGatewayAccount);

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->never();

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->never();

        $this->accountService
            ->shouldReceive('save')
            ->never();

        $this->missionRepository
            ->shouldReceive('update')
            ->once()
            ->andReturn($missionModel);

        // Set response data
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_UPDATED');

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage)
            ->andReturn($jsonResponse);

        $this->expectsEvents(UserActivityLogEvent::class);

        $externalMock = $this->mock('overload:App\Models\NotificationType');
        $externalMock
            ->shouldReceive('where')
            ->once()
            ->with('notification_type', config('constants.notification_type_keys.NEW_MISSIONS'))
            ->andReturnSelf();

        $externalMock
            ->shouldReceive('first')
            ->once()
            ->andReturn((object) [
                'notification_type_id' => 'type'
            ]);

        $response = $this->missionController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUpdateOrganizationGatewayAccountMissionButNoDb()
    {
        $missionId = '1';
        $input = [
            'organization' => [
                'organization_id' => rand(),
                'name' => 'test name'
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'EUR',
                'goal_amount' => '1000'
            ]
        ];

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $requestData = new Request($input);
        $organizationModel = new Organization();
        $missionModel = new Mission();
        $missionModel->mission_id = $missionId;
        $missionModel->mission_type = config('constants.tenant_settings.DONATION');

        $jsonResponse = new JsonResponse();

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($requestData)
            ->andReturn([
                config('constants.tenant_settings.DONATION')
            ]);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.DONATION'),
                $requestData
            )
            ->andReturn(true);

        $this->helpers
            ->shouldReceive('isValidTenantCurrency')
            ->once()
            ->with($requestData, $requestData->get('donation_attribute')['goal_amount_currency'])
            ->andReturn(true);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $requestData->organization['organization_id'],
            'name' => $requestData->organization['name']
        ]);
        $this->missionRepository
            ->shouldReceive('saveOrganization')
            ->once()
            ->with($requestData)
            ->andReturn($organizationObject);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($organizationModel);

        $this->languageHelper
            ->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($requestData)
            ->andReturn((object) [
                'language_id' => 1,
                'code' => 'en',
                'name' => 'English',
                'default' => '1'
            ]);

        $this->missionRepository
            ->shouldReceive('find')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $this->missionRepository
            ->shouldReceive('getMissionDetailsFromId')
            ->once()
            ->with($missionId, 1)
            ->andReturn($missionModel);

        $this->accountService
            ->shouldReceive('getByOrgId')
            ->once()
            ->with($requestData->input('organization.organization_id'))
            ->andReturn(null);

        $this->stripePaymentGateway
            ->shouldReceive('getAccount')
            ->never();

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->never();

        $this->accountService
            ->shouldReceive('save')
            ->never();

        $this->missionRepository
            ->shouldReceive('update')
            ->never();

        // Set response data
        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_ORGANIZATION_PAYMENT_GATEWAY_ACCOUNT'),
                'Organization payment_gateway and payment_gateway_account_id is required'
            );

        $response = $this->missionController->update($requestData, $missionId);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test show method on mission controller
    *
    * @return void
    */
    public function testShow()
    {
        $data = [
            'with_donation_statistics' => true
        ];
        $request = new Request($data);
        $missionId = rand(50000, 70000);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(
                config('constants.tenant_settings.MISSION_IMPACT'),
                $request
            )
            ->andReturn(true);

        $mission = new Mission();
        $mission->id = $missionId;
        $mission->mission_type = config('constants.mission_type.DONATION');
        $mission->donationAttribute = true;

        $this->missionRepository
            ->shouldReceive('find')
            ->once()
            ->with($missionId, true)
            ->andReturn($mission);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($request)
            ->andReturn([
                config('constants.tenant_settings.DONATION_MISSION')
            ]);

        $data = $mission->toArray();
        $data['donation_statistics'] = [
            'content' => 'value'
        ];

        $this->donationService
            ->shouldReceive('getMissionDonationStatistics')
            ->once()
            ->with($missionId)
            ->andReturn($data['donation_statistics']);

        $this->responseHelper->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_MISSION_FOUND'),
                $data,
                false
            )
            ->andReturn(new JsonResponse());

        $response = $this->missionController->show(
            $request,
            $missionId
        );
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Create a new service instance.
     *
     * @param  App\Repositories\Mission\MissionRepository $missionRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param  Illuminate\Http\Request $request
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @param  App\Repositories\MissionMedia\MissionMediaRepository $missionMediaRepository
     * @param  App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @param  App\Repositories\Notification\NotificationRepository $notificationRepository
     * @param App\Repositories\Organization\OrganizationRepository $organizationRepository
     * @param  App\Services\Mission\ModelsService $modelService
     * @param  App\Helpers\Helpers $helpers
     * @return MissionController
     */
    private function getController(
        MissionRepository $missionRepository,
        ResponseHelper $responseHelper,
        Request $request,
        LanguageHelper $languageHelper,
        MissionMediaRepository $missionMediaRepository,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        NotificationRepository $notificationRepository,
        OrganizationRepository $organizationRepository,
        ModelsService $modelService,
        Helpers $helpers = null
    ) {
        return new MissionController(
            $missionRepository,
            $responseHelper,
            $request,
            $languageHelper,
            $missionMediaRepository,
            $tenantActivatedSettingRepository,
            $notificationRepository,
            $organizationRepository,
            $modelService,
            $helpers ?? $this->mock(Helpers::class),
            $this->accountService,
            $this->paymentGatewayFactory,
            $this->donationService
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

    /**
    * get json reponse
    *
    * @param class name
    *
    * @return JsonResponse
    */
    private function getJson($class)
    {
        return new JsonResponse($class);
    }

    /**
     * Create a new service instance.
     *
     * @param  App\Models\Mission $mission
     * @param  App\Models\TimeMission $timeMission
     * @param  App\Models\MissionLanguage $missionLanguage
     * @param  App\Models\MissionDocument $missionDocument
     * @param  App\Models\FavouriteMission $favouriteMission
     * @param  App\Models\MissionSkill $missionSkill
     * @param  App\Models\MissionRating $missionRating
     * @param  App\Models\MissionApplication $missionApplication
     * @param  App\Models\City $city
     * @param  App\Models\MissionTab $missionTab
     * @param  App\Models\MissionTabLanguage $missionTabLanguage
     * @return void
     */
    private function getServices(
        Mission $mission,
        TimeMission $timeMission,
        MissionLanguage $missionLanguage,
        MissionDocument $missionDocument,
        FavouriteMission $favouriteMission,
        MissionSkill $missionSkill,
        MissionRating $missionRating,
        MissionApplication $missionApplication,
        City $city,
        MissionTab $missionTab,
        MissionTabLanguage $missionTabLanguage
    ) {
        return new ModelsService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionTab,
            $missionTabLanguage
        );
    }
}
