<?php

namespace Tests\Unit\Http\Repositories\Mission;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\S3Helper;
use App\Libraries\Amount;
use App\Models\City;
use App\Models\DonationAttribute;
use App\Models\FavouriteMission;
use App\Models\Mission;
use App\Models\MissionApplication;
use App\Models\MissionDocument;
use App\Models\MissionImpact;
use App\Models\MissionImpactDonation;
use App\Models\MissionLanguage;
use App\Models\MissionRating;
use App\Models\MissionSkill;
use App\Models\MissionTab;
use App\Models\MissionTabLanguage;
use App\Models\Organization;
use App\Models\TimeMission;
use App\Repositories\Country\CountryRepository;
use App\Repositories\Donation\DonationRepository;
use App\Repositories\ImpactDonationMission\ImpactDonationMissionRepository;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\MissionImpact\MissionImpactRepository;
use App\Repositories\MissionMedia\MissionMediaRepository;
use App\Repositories\MissionTab\MissionTabRepository;
use App\Repositories\MissionUnitedNationSDG\MissionUnitedNationSDGRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Services\Mission\ModelsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Mockery;
use Ramsey\Uuid\Uuid;
use TestCase;

class MissionRepositoryTest extends TestCase
{
    private $mission;
    private $timeMission;
    private $missionLanguage;
    private $missionDocument;
    private $favouriteMission;
    private $missionSkill;
    private $missionRating;
    private $missionApplication;
    private $missionTab;
    private $missionTabLanguage;
    private $city;
    private $languageHelper;
    private $helpers;
    private $s3Helper;
    private $missionMediaRepository;
    private $modelsService;
    private $missionTabRepository;
    private $collection;
    private $countryRepository;
    private $donationAttribute;
    private $missionImpact;
    private $missionUnitedNationSDGRepository;
    private $missionImpactRepository;
    private $missionImpactDonation;
    private $impactDonationMissionRepository;
    private $donationRepository;
    private $organization;
    private $tenantActivatedSettingRepository;
    private $missionRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->mission = $this->mock(Mission::class);
        $this->timeMission = $this->mock(TimeMission::class);
        $this->missionLanguage = $this->mock(MissionLanguage::class);
        $this->missionDocument = $this->mock(MissionDocument::class);
        $this->favouriteMission = $this->mock(FavouriteMission::class);
        $this->missionSkill = $this->mock(MissionSkill::class);
        $this->missionRating = $this->mock(MissionRating::class);
        $this->missionApplication = $this->mock(MissionApplication::class);
        $this->missionTab = $this->mock(MissionTab::class);
        $this->missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $this->city = $this->mock(City::class);
        $this->languageHelper = $this->mock(LanguageHelper::class);
        $this->helpers = $this->mock(Helpers::class);
        $this->s3Helper = $this->mock(S3Helper::class);
        $this->missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $this->missionTabRepository = $this->mock(MissionTabRepository::class);
        $this->collection = $this->mock(Collection::class);
        $this->countryRepository = $this->mock(CountryRepository::class);
        $this->donationAttribute = $this->mock(DonationAttribute::class);
        $this->missionImpact = $this->mock(MissionImpact::class);
        $this->missionUnitedNationSDGRepository = $this->mock(MissionUnitedNationSDGRepository::class);
        $this->missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $this->missionImpactDonation = $this->mock(MissionImpactDonation::class);
        $this->impactDonationMissionRepository = $this->mock(ImpactDonationMissionRepository::class);
        $this->donationRepository = $this->mock(DonationRepository::class);
        $this->organization = $this->mock(Organization::class);
        $this->tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);

        $this->modelsService = new ModelsService(
            $this->mission,
            $this->timeMission,
            $this->missionLanguage,
            $this->missionDocument,
            $this->favouriteMission,
            $this->missionSkill,
            $this->missionRating,
            $this->missionApplication,
            $this->city,
            $this->missionImpactDonation,
            $this->missionImpact,
            $this->organization,
            $this->missionTab,
            $this->missionTabLanguage,
            $this->donationAttribute
        );

        $this->missionRepository = new MissionRepository(
            $this->languageHelper,
            $this->helpers,
            $this->s3Helper,
            $this->countryRepository,
            $this->missionMediaRepository,
            $this->modelsService,
            $this->impactDonationMissionRepository,
            $this->missionImpactRepository,
            $this->tenantActivatedSettingRepository,
            $this->missionUnitedNationSDGRepository,
            $this->missionTabRepository,
            $this->donationRepository
        );
    }
    /**
    * @testdox Test donation attribute add success
    *
    * @return void
    */
    public function testAddDonationMissionSuccess()
    {
        $requestParams = [
            'theme_id' => 1,
            'start_date' => '2019-05-15 10:40:00',
            'end_date' => '2022-10-15 10:40:00',
            'total_seats' => rand(10, 1000),
            'mission_type' => config('constants.mission_type.DONATION'),
            'publication_status' => config('constants.publication_status.APPROVED'),
            'organisation_id' => 1,
            'organisation_name' => str_random(10),
            'availability_id' => 1,
            'location' => [
                'city_id' => 1,
                'country_id' => 233,
                'country_code' => 'US'
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'CAD',
                'goal_amount' => 253,
                'show_goal_amount' => 1,
                'show_donation_percentage' => 0,
                'show_donation_meter' => 0,
                'show_donation_count' => 0,
                'show_donors_count' => 0,
                'disable_when_funded' => 0,
                'is_disabled' => 0
            ],
            'mission_detail'=> [
                [
                    'lang' => 'en',
                    'title' => 'New Organization Mission created',
                    'short_description' => 'this is testing api with all mission details'
                    ,
                    'objective' => 'To test and check',
                    'label_goal_achieved' => 'test percentage',
                    'label_goal_objective' => 'check test percentage',
                    'section' => [
                        [
                            'title' => 'Section title',
                            'description' => 'Section description'
                        ]
                    ],
                    'custom_information' => [
                        [
                            'title' => 'Customer info',
                            'description' => 'Description of customer info'
                        ]
                    ]
                ]
            ],
            'organization' => [
                'organization_id' => '1',
                'name' => 'name'
            ]
        ];

        $languagesArray = [
            (object)[
                'language_id' => 1,
                'name' => 'English',
                'code' => 'en',
                'status' => '1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            (object)[
                'language_id' => 2,
                'name' => 'French',
                'code' => 'fr',
                'status' => '1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ]
        ];

        $request = new Request($requestParams);

        $mission = $this->mock(Mission::class);
        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $city = $this->mock(City::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $s3Helper = $this->mock(S3Helper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $modelsService = $this->mock(ModelsService::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $collection = $this->mock(Collection::class);
        $countryRepository = $this->mock(CountryRepository::class);
        $donationAttribute = $this->mock(DonationAttribute::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $missionUnitedNationSDGRepository = $this->mock(MissionUnitedNationSDGRepository::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $missionImpactDonation = $this->mock(MissionImpactDonation::class);
        $impactDonationMissionRepository = $this->mock(ImpactDonationMissionRepository::class);
        $donationRepository = $this->mock(DonationRepository::class);

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $request->organization['organization_id'],
            'name' => $request->organization['name']
        ]);
        $organization = $this->mock(Organization::class);
        $request->request->add([
            'organization_id' => $request->organization['organization_id']
        ]);

        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($request)
            ->andReturn([config('constants.tenant_settings.DONATION_MISSION')]);

        $modelsService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpactDonation,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage,
            $donationAttribute
        );
        $missionModel = new Mission();
        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];
        $missionModel->mission_id = 13;
        $modelsService->mission
            ->shouldReceive('create')
            ->once()
            ->andReturn($missionModel);

        $collectionLanguageData = collect($languagesArray);

        $languageHelper->shouldReceive('getLanguages')
            ->once()
            ->andReturn($collectionLanguageData);
        $languageHelper->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($request)
            ->andReturn($languagesArray[0]);

        $countryId = $requestParams['location']['country_id'];

        $countryRepository->shouldReceive('getCountryId')
            ->once()
            ->with($request->location['country_code'])
            ->andReturn($countryId);

        $modelsService->missionLanguage->shouldReceive('create')
            ->once()
            ->andReturn(false);

        $modelsService->donationAttribute->shouldReceive('create')
            ->once()
            ->andReturn(false);

        $tenantName = str_random(10);
        $helpers->shouldReceive('getSubDomainFromRequest')
            ->once()
            ->with($request)
            ->andReturn($tenantName);

        $repository = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelsService,
            $impactDonationMissionRepository,
            $missionImpactRepository,
            $tenantActivatedSettingRepository,
            $missionUnitedNationSDGRepository,
            $missionTabRepository,
            $donationRepository
        );

        $response = $repository->store($request);

        $this->assertInstanceOf(Mission::class, $response);
    }

    /**
    * @testdox Test donation attribute update success
    *
    * @return void
    */
    public function testUpdateDonationMissionSuccess()
    {

        $requestParams = [
            'mission_type' => config('constants.mission_type.DONATION'),
            'location' => [
                'city_id' => 1,
                'country_id' => 233,
                'country_code' => 'US'
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'CAD',
                'goal_amount' => 253,
                'show_goal_amount' => 1,
                'show_donation_percentage' => 0,
                'show_donation_meter' => 0,
                'show_donation_count' => 0,
                'show_donors_count' => 0,
                'disable_when_funded' => 0,
                'is_disabled' => 0
            ]
        ];

        $languagesArray = [
            (object)[
                'language_id' => 1,
                'name' => 'English',
                'code' => 'en',
                'status' => '1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null
            ],
            (object)[
                'language_id' => 2,
                'name' => 'French',
                'code' => 'fr',
                'status' => '1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null
            ]
        ];

        $request = new Request($requestParams);

        $mission = $this->mock(Mission::class);
        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $city = $this->mock(City::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $s3Helper = $this->mock(S3Helper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $modelsService = $this->mock(ModelsService::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $collection = $this->mock(Collection::class);
        $countryRepository = $this->mock(CountryRepository::class);
        $donationAttribute = $this->mock(DonationAttribute::class);
        $organization = $this->mock(Organization::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $missionUnitedNationSDGRepository = $this->mock(MissionUnitedNationSDGRepository::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $missionImpactDonation = $this->mock(MissionImpactDonation::class);
        $impactDonationMissionRepository = $this->mock(ImpactDonationMissionRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $donationRepository = $this->mock(DonationRepository::class);

        $modelsService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpactDonation,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage,
            $donationAttribute
        );

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $missionModel = new Mission();
        $missionModel->donationAttribute = $donationAttribute;
        $missionId = 13;
        $missionModel->mission_id = $missionId;
        $modelsService->mission
            ->shouldReceive('findOrFail')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $collectionLanguageData = collect($languagesArray);

        $languageHelper->shouldReceive('getLanguages')
            ->once()
            ->andReturn($collectionLanguageData);
        $languageHelper->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($request)
            ->andReturn($languagesArray[0]);

        $countryId = $requestParams['location']['country_id'];

        $countryRepository->shouldReceive('getCountryId')
            ->once()
            ->with($request->location['country_code'])
            ->andReturn($countryId);

        $tenantName = str_random(10);
        $helpers->shouldReceive('getSubDomainFromRequest')
            ->once()
            ->with($request)
            ->andReturn($tenantName);

        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($request)
            ->andReturn([]);

        $isDonationSettingEnabled = true;
        $repository = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelsService,
            $impactDonationMissionRepository,
            $missionImpactRepository,
            $tenantActivatedSettingRepository,
            $missionUnitedNationSDGRepository,
            $missionTabRepository,
            $donationRepository
        );

        $response = $repository->update($request, $missionId);

        $this->assertInstanceOf(Mission::class, $response);
    }

    /**
    * @testdox Test mission tab deleted by mission_tab_id success
    *
    * @return void
    */
    public function testDeleteMissionTabByMissionTabIdSuccess()
    {
        $missionTabId = str_random(8).'-'.str_random(4).'-'.str_random(4).'-'.str_random(4).'-'.str_random(12);

        $mission = $this->mock(Mission::class);
        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $s3Helper = $this->mock(S3Helper::class);
        $countryRepository = $this->mock(CountryRepository::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $missionUnitedNationSDGRepository = $this->mock(MissionUnitedNationSDGRepository::class);
        $collection = $this->mock(Collection::class);
        $organization = $this->mock(Organization::class);
        $donationAttribute = $this->mock(DonationAttribute::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $missionImpactDonation = $this->mock(MissionImpactDonation::class);
        $impactDonationMissionRepository = $this->mock(ImpactDonationMissionRepository::class);
        $donationRepository = $this->mock(DonationRepository::class);

        $modelService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpactDonation,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage,
            $donationAttribute
        );

        $modelService->missionTab
            ->shouldReceive('deleteMissionTabByMissionTabId')
            ->with($missionTabId)
            ->andReturn();

        $repository = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelService,
            $impactDonationMissionRepository,
            $missionImpactRepository,
            $tenantActivatedSettingRepository,
            $missionUnitedNationSDGRepository,
            $missionTabRepository,
            $donationRepository
        );

        $response = $repository->deleteMissionTabByMissionTabId($missionTabId);
    }

    /**
     * @testdox Test store method focus on document upload on repository
     */
    public function testStoreDocumentUpload()
    {
        $organizationId = Uuid::uuid4()->toString();
        $params = [
            'organization' => [
                'organization_id' => $organizationId
            ],
            'location' => [
                'city_id' => 1,
                'country_code' => 'PH'
            ],
            'theme_id' => 1,
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
                'total_seats' => rand(5000, 10000),
                'availability_id' => 1,
                'is_virtual' => 1
            ]
        ];

        $request = new Request();
        $request->query->add($params);

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $organizationObject = factory(Organization::class)->make([
            'organization_id' => $request->organization['organization_id'],
            'name' => 'organizationName'
        ]);
        $organization = $this->mock(Organization::class);
        $request->request->add([
            'organization_id' => $request->organization['organization_id']
        ]);

        $languages = new Collection([
            [
                'code' => 'en'
            ]
        ]);

        $languageHelper = $this->mock(LanguageHelper::class);
        $languageHelper->shouldReceive('getLanguages')
            ->once()
            ->andReturn($languages);

        $languageHelper->shouldReceive('getDefaultTenantLanguage')
            ->once()
            ->with($request)
            ->andReturn($defaultLanguage);

        $countryId = 1;
        $countryRepository = $this->mock(CountryRepository::class);
        $countryRepository->shouldReceive('getCountryId')
            ->once()
            ->with($params['location']['country_code'])
            ->andReturn($countryId);

        $missionData = [
            'theme_id' => 1,
            'city_id' => 1,
            'country_id' => $countryId,
            'start_date' => null,
            'end_date' => null,
            'publication_status' => $request->publication_status,
            'organization_id' => $organizationObject->organization_id,
            'organisation_detail' => null,
            'mission_type' => $request->mission_type
        ];

        $missionObject = new Mission();
        $missionObject->setAttribute('mission_id', 1);

        $hasOne = $this->mock(HasOne::class);
        $hasOne->shouldReceive('create')
            ->once()
            ->andReturn(true);
        $modelService = $this->mock(ModelsService::class);
        $mission = $this->mock(Mission::class);
        $mission->shouldReceive('create')
            ->once()
            ->with($missionData)
            ->andReturn($mission)
            ->shouldReceive('volunteeringAttribute')
            ->once()
            ->andReturn($hasOne)
            ->shouldReceive('getAttribute')
            ->twice()
            ->with('mission_id')
            ->andReturn($missionObject->mission_id);

        $tenantName = 'tenantName';

        $helpers = $this->mock(Helpers::class);
        $helpers->shouldReceive('getSubDomainFromRequest')
            ->once()
            ->with($request)
            ->andReturn($tenantName);

        $documentId = 1;
        $documentObject = factory(MissionDocument::class)->make([
            'mission_document_id' => $documentId,
            'sort_order' => $request->documents[0]['sort_order'],
            'document_path' => $request->documents[0]['document_path']
        ]);

        $missionDocument = $this->mock(MissionDocument::class);
        $missionDocument->shouldReceive('create')
            ->once()
            ->with([
                'mission_id' => $missionObject->mission_id,
                'sort_order' => $documentObject->sort_order
            ])
            ->andReturn($documentObject);

        $s3Helper = $this->mock(S3Helper::class);
        $s3Helper->shouldReceive('uploadFileOnS3Bucket')
            ->once()
            ->with(
                $documentObject->document_path,
                $tenantName,
                "missions/$missionObject->mission_id/documents/$documentId"
            )
            ->andReturn($documentObject->document_path);

        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $missionUnitedNationSDGRepository = $this->mock(MissionUnitedNationSDGRepository::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $donationAttribute = $this->mock(DonationAttribute::class);
        $missionImpactDonation = $this->mock(MissionImpactDonation::class);
        $impactDonationMissionRepository = $this->mock(ImpactDonationMissionRepository::class);
        $donationRepository = $this->mock(DonationRepository::class);

        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($request)
            ->andReturn([config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION')]);

        $modelService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpactDonation,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage,
            $donationAttribute
        );

        $response = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelService,
            $impactDonationMissionRepository,
            $missionImpactRepository,
            $tenantActivatedSettingRepository,
            $missionUnitedNationSDGRepository,
            $missionTabRepository,
            $donationRepository
        )->store($request);
    }

    /**
     * @testdox Test update method focus on document upload on repository
     */
    public function testUpdateDocumentUpload()
    {
        $params = [
            'publication_status' => true,
            'mission_type' => config('constants.mission_type.GOAL'),
            'documents' => [
                [
                    'document_id' => 1,
                    'sort_order' => 0,
                    'document_path' => 'http://admin-m7pww5ymmj28.back.staging.optimy.net/assets/images/optimy-logo.png'
                ]
            ]
        ];
        $request = new Request();
        $request->query->add($params);

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $languages = new Collection([
            [
                'code' => 'en'
            ]
        ]);

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $languageHelper = $this->mock(LanguageHelper::class);
        $languageHelper->shouldReceive('getLanguages')
            ->once()
            ->andReturn($languages);

        $languageHelper->shouldReceive('getDefaultTenantLanguage')
        ->once()
        ->with($request)
        ->andReturn($defaultLanguage);

        $missionId = 1;
        $missionObject = new Mission();
        $missionObject->setAttribute('mission_id', $missionId);

        $mission = $this->mock(Mission::class);
        $mission->shouldReceive('findOrFail')
            ->once()
            ->with($missionId)
            ->andReturn($missionObject);

        $tenantName = 'tenantName';

        $helpers = $this->mock(Helpers::class);
        $helpers->shouldReceive('getSubDomainFromRequest')
            ->once()
            ->with($request)
            ->andReturn($tenantName);

        $documentId = $request->documents[0]['document_id'];
        $documentObject = factory(MissionDocument::class)->make([
            'mission_document_id' => $documentId,
            'sort_order' => $request->documents[0]['sort_order'],
            'document_path' => $request->documents[0]['document_path']
        ]);

        $missionDocument = $this->mock(MissionDocument::class);
        $missionDocument->shouldReceive('createOrUpdateDocument')
            ->once()
            ->with([
                'mission_id' => $missionId,
                'mission_document_id' => $documentId
            ], [
                'mission_id' => $missionId,
                'sort_order' => 0
            ])
            ->andReturn($documentObject);

        $s3Helper = $this->mock(S3Helper::class);
        $s3Helper->shouldReceive('uploadFileOnS3Bucket')
        ->once()
        ->with(
            $documentObject->document_path,
            $tenantName,
            "missions/$missionId/documents/$documentId"
        )
        ->andReturn($documentObject->document_path);

        $countryRepository = $this->mock(CountryRepository::class);
        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $missionUnitedNationSDGRepository = $this->mock(MissionUnitedNationSDGRepository::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $organization = $this->mock(Organization::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $donationAttribute = $this->mock(DonationAttribute::class);
        $missionImpactDonation = $this->mock(MissionImpactDonation::class);
        $impactDonationMissionRepository = $this->mock(ImpactDonationMissionRepository::class);
        $donationRepository = $this->mock(DonationRepository::class);

        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
            ->once()
            ->with($request)
            ->andReturn([config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION')]);

        $modelService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpactDonation,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage,
            $donationAttribute
        );

        $response = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelService,
            $impactDonationMissionRepository,
            $missionImpactRepository,
            $tenantActivatedSettingRepository,
            $missionUnitedNationSDGRepository,
            $missionTabRepository,
            $donationRepository
        )->update($request, $missionId);
    }

    /**
     * @testdox Test is mission eligible for donations
     */
    public function testGetIsMissionEligibleForDonation()
    {
        $mission = $this->mock(Mission::class);
        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $city = $this->mock(City::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $s3Helper = $this->mock(S3Helper::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $modelsService = $this->mock(ModelsService::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $collection = $this->mock(Collection::class);
        $countryRepository = $this->mock(CountryRepository::class);
        $donationAttribute = $this->mock(DonationAttribute::class);
        $organization = $this->mock(Organization::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $missionImpactDonation = $this->mock(MissionImpactDonation::class);
        $missionUnitedNationSDGRepository = $this->mock(MissionUnitedNationSDGRepository::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $impactDonationMissionRepository = $this->mock(ImpactDonationMissionRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $donationRepository = $this->mock(DonationRepository::class);

        $donationAttribute = (new DonationAttribute)
            ->setAttribute('is_disabled', false)
            ->setAttribute('disable_when_funded', true)
            ->setAttribute('goal_amount', 1000);

        $modelsService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpactDonation,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage,
            $donationAttribute
        );

        $request = new Request();
        $tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->with('donation', $request)
            ->andReturn(true);

        $missionModel = new Mission();
        $missionModel->donationAttribute = $donationAttribute;
        $missionId = 13;
        $missionModel->mission_id = $missionId;
        $missionModel->start_date = Carbon::now()->subDays(1);
        $missionModel->end_date = Carbon::now()->addDays(5);

        $modelsService->mission
            ->shouldReceive('findOrFail')
            ->once()
            ->with($missionId)
            ->andReturn($missionModel);

        $donationRepository
            ->shouldReceive('getMissionTotalDonationAmount')
            ->once()
            ->with($missionId)
            ->andReturn(new \App\Libraries\Amount(1000));

        $repository = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelsService,
            $impactDonationMissionRepository,
            $missionImpactRepository,
            $tenantActivatedSettingRepository,
            $missionUnitedNationSDGRepository,
            $missionTabRepository,
            $donationRepository
        );

        $response = $repository->isEligibleForDonation($request, $missionId);
    }

    /**
     * @testdox test getDonationStatistics method
     */
    public function testGetDonationStatistics()
    {
        $mission = new Mission();
        $mission->setAttribute('mission_id', 41);

        $missionIds = [
            $mission->mission_id
        ];
        $this->mission
            ->shouldReceive('selectRaw')
            ->once()
            ->with('
                mission.mission_id,
                COUNT(donation.id) as count,
                COUNT(DISTINCT donation.user_id) as donors,
                SUM(payment.amount) as total_amount
            ')
            ->andReturnSelf()
            ->shouldReceive('join')
            ->once()
            ->with('donation', 'donation.mission_id', '=', 'mission.mission_id')
            ->andReturnSelf()
            ->shouldReceive('join')
            ->once()
            ->with('payment', 'payment.id', '=', 'donation.payment_id')
            ->andReturnSelf()
            ->shouldReceive('where')
            ->once()
            ->with('payment.status', config('constants.payment_statuses.SUCCESS'))
            ->andReturnSelf()
            ->shouldReceive('whereIn')
            ->once()
            ->with('mission.mission_id', $missionIds)
            ->andReturnSelf()
            ->shouldReceive('groupBy')
            ->once()
            ->with('mission.mission_id')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->once()
            ->andReturn($mission);

        $response = $this->missionRepository->getDonationStatistics($missionIds);
        $this->assertInstanceOf(Mission::class, $response);
    }

    /**
     * Create a new respository instance.
     *
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @param  App\Helpers\Helpers $helpers
     * @param  App\Helpers\S3Helper $s3helper
     * @param  App\Repositories\Country\CountryRepository $countryRepository
     * @param  App\Repositories\MissionMedia\MissionMediaRepository $missionMediaRepository
     * @param  App\Services\Mission\ModelsService $modelsService
     * @param  App\Repositories\ImpactDonationMission\ImpactDonationMissionRepository
     * @param  App\Repositories\MissionImpact\MissionImpactRepository $missionImpactRepository
     * @param  App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @param  App\Repositories\MissionMedia\MissionTabRepository $missionTabRepository
     * @param  App\Repositories\MissionMedia\MissionUnitedNationSDGRepository $missionUnitedNationSDGRepository
     * @param  App\Repositories\Donation\DonationRepository $donationRepository
     * @return void
     */
    private function getRepository(
        LanguageHelper $languageHelper,
        Helpers $helpers,
        S3Helper $s3helper,
        CountryRepository $countryRepository,
        MissionMediaRepository $missionMediaRepository,
        ModelsService $modelsService,
        ImpactDonationMissionRepository $impactDonationMissionRepository,
        MissionImpactRepository $missionImpactRepository,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        MissionUnitedNationSDGRepository $missionUnitedNationSDGRepository,
        MissionTabRepository $missionTabRepository,
        DonationRepository $donationRepository
    ) {

        return new MissionRepository(
            $languageHelper,
            $helpers,
            $s3helper,
            $countryRepository,
            $missionMediaRepository,
            $modelsService,
            $impactDonationMissionRepository,
            $missionImpactRepository,
            $tenantActivatedSettingRepository,
            $missionUnitedNationSDGRepository,
            $missionTabRepository,
            $donationRepository
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
     * @param  App\Models\MissionImpactDonation $missionImpactDonation
     * @param  App\Models\MissionImpact $missionImpact
     * @param  App\Models\Organization $organization
     * @param  App\Models\MissionImpactDonation $missionImpactDonation
     * @param  App\Models\MissionTab $missionTab
     * @param  App\Models\MissionTabLanguage $missionTabLanguage
     * @param  App\Models\DonationAttribute $donationAttribute
     * @return void
     */
    public function modelService(
        Mission $mission,
        TimeMission $timeMission,
        MissionLanguage $missionLanguage,
        MissionDocument $missionDocument,
        FavouriteMission $favouriteMission,
        MissionSkill $missionSkill,
        MissionRating $missionRating,
        MissionApplication $missionApplication,
        City $city,
        MissionImpactDonation $missionImpactDonation,
        MissionImpact $missionImpact,
        Organization $organization,
        MissionTab $missionTab,
        MissionTabLanguage $missionTabLanguage,
        DonationAttribute $donationAttribute
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
            $missionImpactDonation,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage,
            $donationAttribute
        );
    }
}
