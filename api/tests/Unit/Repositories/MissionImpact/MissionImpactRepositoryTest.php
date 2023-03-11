<?php
    
namespace Tests\Unit\Repositories\MissionImpact;

use DB;
use Mockery;
use TestCase;
use App\Models\Mission;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\LanguageHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Models\MissionImpact;
use App\Services\Mission\ModelsService;
use App\Models\MissionImpactLanguage;
use App\Repositories\MissionImpact\MissionImpactRepository;
use App\Helpers\S3Helper;
use App\Helpers\Helpers;

class MissionImpactRepositoryTest extends TestCase
{
    /**
    * @testdox Test store success
    *
    * @return void
    */
    public function testImpactMissionStoreSuccess()
    {
        $data = [
            'icon_path' => str_random(100),
            'sort_key' => rand(50000, 70000),
            'translations' => [
                [
                    'language_code' => 'fr',
                    'content' => str_random(160)
                ]
            ]
        ];

        $languagesData = [
            (object)[
                'language_id' => 1,
                'name'=> 'English',
                'code'=> 'en',
                'status'=> '1',
                'created_at'=> null,
                'updated_at'=> null,
                'deleted_at'=> null,
            ],
            (object)[
                'language_id' => 2,
                'name' => 'French',
                'code' => 'fr',
                'status'=>'1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ]
        ];

        $collectionLanguageData = collect($languagesData);
        $missionId = rand(50000, 70000);
        $defaultTenantLanguageId = 1;
        $tenantName = str_random(20);
        $iconPath = str_random(200);

        $mission = $this->mock(Mission::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $missionImpactLanguage = $this->mock(MissionImpactLanguage::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $collection = $this->mock(Collection::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $s3helper = $this->mock(S3Helper::class);
        $helpers = $this->mock(Helpers::class);

        $languageHelper->shouldReceive('getLanguages')
        ->once()
        ->andReturn($collection);

        $missionImpactModel = new MissionImpact();
        $missionImpactModel->id = rand(50000, 70000);

        $missionImpact->shouldReceive('create')
        ->once()
        ->andReturn($missionImpactModel);

        $s3helper->shouldReceive('uploadFileOnS3Bucket')
        ->once()
        ->andReturn($iconPath);

        $collection->shouldReceive('where')
        ->once()
        ->with('code', $data['translations'][0]['language_code'])
        ->andReturn($collectionLanguageData);

        $missionImpactLanguage->shouldReceive('create')
        ->once()
        ->andReturn($missionImpactLanguage);
        
        $repository = $this->getRepository(
            $missionImpact,
            $missionImpactLanguage,
            $languageHelper,
            $s3helper,
            $helpers
        );

        $response = $repository->store($data, $missionId, $defaultTenantLanguageId, $tenantName);
    }

    /**
    * @testdox Test update success
    *
    * @return void
    */
    public function testImpactMissionssUpdateSuccess()
    {
        $data = [
            'mission_impact_id' => str_random(36),
            'icon_path' => str_random(100),
            'sort_key' => rand(10000, 100000),
            'translations' => [
                [
                    'language_code' => 'en',
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
                'status' =>'1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ]
        ];

        $collectionLanguageData = collect($languagesData);
        $missionId = rand(10000, 100000);
        $defaultTenantLanguageId = 1;
        $tenantName = str_random(20);

        $mission = $this->mock(Mission::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $missionImpactLanguage = $this->mock(MissionImpactLanguage::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $collection = $this->mock(Collection::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $s3helper = $this->mock(S3Helper::class);
        $helpers = $this->mock(Helpers::class);

        $languageData = $languageHelper->shouldReceive('getLanguages')
        ->once()
        ->andReturn($collection);

        $missionImpact->shouldReceive('where')
        ->once()
        ->with(['mission_impact_id'=>$data['mission_impact_id']])
        ->andReturn($missionImpact);

        $missionImpact->shouldReceive('update')
        ->once()
        ->with(['sort_key'=>$data['sort_key']])
        ->andReturn($missionImpact);

        $missionImpact->shouldReceive('where')
        ->once()
        ->with(['mission_impact_id'=>$data['mission_impact_id']])
        ->andReturn($missionImpact);

        $iconPath = str_random(100);
        
        $s3helper->shouldReceive('uploadFileOnS3Bucket')
        ->once()
        ->andReturn($iconPath);
        
        $missionImpact->shouldReceive('update')
        ->once()
        ->with(['icon_path'=> $iconPath])
        ->andReturn($missionImpact);

        $collection->shouldReceive('where')
        ->once()
        ->with('code', $data['translations'][0]['language_code'])
        ->andReturn($collectionLanguageData);

        $missionImpactLanguage->shouldReceive('createOrUpdateMissionImpactTranslation')
        ->once()
        ->andReturn();

        $repository = $this->getRepository(
            $missionImpact,
            $missionImpactLanguage,
            $languageHelper,
            $s3helper,
            $helpers
        );

        $response = $repository->update($data, $missionId, $defaultTenantLanguageId, $tenantName);
    }

    /**
     * Create a new ImpactMission repository instance.
     *
     * @param  App\Models\MissionImpact $missionImpact
     * @param  App\Models\MissionImpactLanguage $missionImpactLanguage
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @param  App\Helpers\S3Helper $s3helper
     * @param  App\Helpers\Helpers
     * @return void
     */
    private function getRepository(
        MissionImpact $missionImpact,
        MissionImpactLanguage $missionImpactLanguage,
        LanguageHelper $languageHelper,
        S3Helper $s3helper,
        Helpers $helpers
    ) {
        return new MissionImpactRepository(
            $missionImpact,
            $missionImpactLanguage,
            $languageHelper,
            $s3helper,
            $helpers
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
