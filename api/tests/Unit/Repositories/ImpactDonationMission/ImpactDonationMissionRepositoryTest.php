<?php
    
namespace Tests\Unit\Repositories\ImpactDonationMission;

use DB;
use Mockery;
use TestCase;
use App\Models\Mission;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\LanguageHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Models\MissionImpactDonation;
use App\Services\Mission\ModelsService;
use App\Models\MissionImpactDonationLanguage;
use App\Repositories\ImpactDonationMission\ImpactDonationMissionRepository;
use App\Models\TimeMission;
use App\Models\MissionLanguage;
use App\Models\MissionDocument;
use App\Models\FavouriteMission;
use App\Models\MissionSkill;
use App\Models\MissionRating;
use App\Models\MissionApplication;
use App\Models\City;
use App\Models\Organization;
use App\Models\MissionTab;
use App\Models\MissionTabLanguage;
use App\Models\MissionImpact;
use App\Models\DonationAttribute;

class ImpactDonationMissionRepositoryTest extends TestCase
{
    /**
    * @testdox Test store success
    *
    * @return void
    */
    public function testImpactDonationStoreSuccess()
    {
        $data = [
            'amount' => 512,
            'translations' => [
                [
                    'language_code' => 'en',
                    'content' => 'this is test impact donation mission in english 2 language.'
                ]
            ]
        ];

        $languagesData = [
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

        $collectionLanguageData = collect($languagesData);
        $missionId = 13;
        $defaultTenantLanguageId = 1;

        $mission = $this->mock(Mission::class);
        $modelService = $this->mock(ModelsService::class);
        $missionImpactDonationLanguage = $this->mock(MissionImpactDonationLanguage::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $collection = $this->mock(Collection::class);
        $missionImpactDonation = $this->mock(MissionImpactDonation::class);

        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $organization = $this->mock(Organization::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $donationAttribute = $this->mock(DonationAttribute::class);

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

        $languageHelper->shouldReceive('getLanguages')
            ->once()
            ->andReturn($collection);

        $modelService->missionImpactDonation->shouldReceive('create')
            ->once()
            ->andReturn(new MissionImpactDonation());

        $collection->shouldReceive('where')
            ->once()
            ->with('code', $data['translations'][0]['language_code'])
            ->andReturn($collectionLanguageData);

        $missionImpactDonationLanguage->shouldReceive('create')
            ->once()
            ->andReturn($missionImpactDonationLanguage);
        
        $repository = $this->getRepository(
            $mission,
            $modelService,
            $missionImpactDonationLanguage,
            $languageHelper
        );

        $response = $repository->store($data, $missionId, $defaultTenantLanguageId);
    }

    /**
    * @testdox Test update success
    *
    * @return void
    */
    public function testImpactDonationUpdateSuccess()
    {
        $data = [
            'impact_donation_id' => str_random(36),
            'amount' => rand(10000, 100000),
            'translations' => [
                [
                    'language_code' => "en",
                    'content' => str_random(160)
                ]
            ]
        ];

        $languagesData = [
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

        $collectionLanguageData = collect($languagesData);
        $missionId = rand(10000, 100000);
        $defaultTenantLanguageId = 1;

        $mission = $this->mock(Mission::class);
        $modelService = $this->mock(ModelsService::class);
        $missionImpactDonationLanguage = $this->mock(MissionImpactDonationLanguage::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $collection = $this->mock(Collection::class);
        $missionImpactDonation = $this->mock(MissionImpactDonation::class);

        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $organization = $this->mock(Organization::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $donationAttribute = $this->mock(DonationAttribute::class);

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

        $languageData = $languageHelper->shouldReceive('getLanguages')
            ->once()
            ->andReturn($collection);

        $modelService->missionImpactDonation->shouldReceive('where')
            ->once()
            ->with(['mission_impact_donation_id' => $data['impact_donation_id']])
            ->andReturn($missionImpactDonation);

        $modelService->missionImpactDonation->shouldReceive('update')
            ->once()
            ->with(['amount' => $data['amount']])
            ->andReturn($missionImpactDonation);

        $collection->shouldReceive('where')
            ->once()
            ->with('code', $data['translations'][0]['language_code'])
            ->andReturn($collectionLanguageData);

        $missionImpactDonationLanguage->shouldReceive('createOrUpdateDonationImpactTranslation')
            ->once()
            ->andReturn();

        $repository = $this->getRepository(
            $mission,
            $modelService,
            $missionImpactDonationLanguage,
            $languageHelper            
        );

        $response = $repository->update($data, $missionId, $defaultTenantLanguageId);
    }

    /**
    * @testdox Test delete imapct donation success
    *
    * @return void
    */
    public function testImpactDonationDeleteSuccess()
    {
        $missionImpactDonationId = str_random(36);

        $mission = $this->mock(Mission::class);
        $modelService = $this->mock(ModelsService::class);
        $missionImpactDonationLanguage = $this->mock(MissionImpactDonationLanguage::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $collection = $this->mock(Collection::class);
        $missionImpactDonation = $this->mock(MissionImpactDonation::class);

        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $organization = $this->mock(Organization::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $donationAttribute = $this->mock(DonationAttribute::class);

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

        $modelService->missionImpactDonation->shouldReceive('findOrFail')
            ->once()
            ->with($missionImpactDonationId)
            ->andReturn($missionImpactDonation);

        $modelService->missionImpactDonation->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $repository = $this->getRepository(
            $mission,
            $modelService,
            $missionImpactDonationLanguage,
            $languageHelper            
        );

        $response = $repository->deleteMissionImpactDonation($missionImpactDonationId);
    }

    /**
     * Create a new ImpactDonationMission repository instance.
     *
     * @param  Mission $mission
     * @param  App\Services\Mission\ModelsService $modelsService
     * @param  App\Models\MissionImpactDonationLanguage $missionImpactDonationLanguage
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    private function getRepository(
        Mission $mission,
        ModelsService $modelsService,
        MissionImpactDonationLanguage $missionImpactDonationLanguage,
        LanguageHelper $languageHelper
    ) {
        return new ImpactDonationMissionRepository(
            $mission,
            $modelsService,
            $missionImpactDonationLanguage,
            $languageHelper
        );
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
     * @param  App\Models\MissionTab $missionTab
     * @param  App\Models\MissionTabLanguage $missionTabLanguage
     * @param  App\Models\DonationAttribute $donationAttribute
     * @return void
     */
    private function modelService(
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
