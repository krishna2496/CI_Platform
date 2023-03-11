<?php

namespace Tests\Unit\Transformations;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use TestCase;
use Closure;
use App\Transformations\AdminMissionTransformable;
use App\Models\Mission;
use Illuminate\Support\Collection;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;

class AdminMissionTransformableTest extends TestCase
{
    public function test_admin_transformation()
    {
        $mission = new Mission();
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
        
        $languages = collect($languagesData);
        $impact['impact'] = [];
        $impact['impact']['mission_impact_id']  = rand();
        $impact['impact']['sort_key']  = 1;
        $impact['impact']['icon_path']  = str_random(30);
        $impact['impact']['mission_impact_language_details'] = [];
        $impact['impact']['mission_impact_language_details'][0]['language_id'] = 1;
        $impact['impact']['mission_impact_language_details'][0]['language_code'] = 'en';
        $impact['impact']['mission_impact_language_details'][0]['content'] = str_random(10);
        $mission->impact = collect($impact);
        
        $this->requestParametersTrait = $this->getObjectForTrait('App\Transformations\AdminMissionTransformable');
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);

        $getRequestParameterReflection = $this->getGetRequestParameterReflection();
        $this->assertEquals(
            $mission,
            $getRequestParameterReflection->invoke($this->requestParametersTrait, $mission, $languages, $tenantActivatedSettingRepository)
        );
    }

    private function getGetRequestParameterReflection()
    {
        $getRequestParameterReflection = new \ReflectionMethod(
            get_class($this->requestParametersTrait),
            'adminTransformMission'
        );

        $getRequestParameterReflection->setAccessible(true);

        return $getRequestParameterReflection;
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
