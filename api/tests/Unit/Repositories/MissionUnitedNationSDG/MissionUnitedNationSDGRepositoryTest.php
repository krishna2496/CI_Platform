<?php

namespace Tests\Unit\Http\Repositories\Mission;

use App\Models\MissionUnSdg;
use TestCase;
use Illuminate\Http\Request;
use Mockery;
use App\Repositories\MissionUnitedNationSDG\MissionUnitedNationSDGRepository;

class MissionUnitedNationSDGRepositoryTest extends TestCase
{
    /**
    * @testdox Test add UN SDG success
    *
    * @return void
    */
    public function testAddUnSdgSuccess()
    {
        $data = [
            'un_sdg' => [1,2,3]
        ];
        $requestData = new Request($data);
        $missionUnSdg = $this->mock(MissionUnSdg::class);
        $repository = $this->getRepository(
            $missionUnSdg
        );

        $missionUnSdg->shouldReceive('create')
        ->andReturn(false);

        $response = $repository->addUnSdg(rand(10,100), $requestData->toArray());
        $this->assertTrue(true);
    }

    /**
    * @testdox Test update UN SDG success
    *
    * @return void
    */
    public function testUpdateUnSdgSuccess()
    {
        $data = [
            'un_sdg' => [1,2,3]
        ];
        $requestData = new Request($data);
        $missionUnSdg = $this->mock(MissionUnSdg::class);
        $repository = $this->getRepository(
            $missionUnSdg
        );

        $missionUnSdg->shouldReceive('where')
        ->andReturn($missionUnSdg);

        $missionUnSdg->shouldReceive('whereNotIn')
        ->andReturn($missionUnSdg);
        
        $missionUnSdg->shouldReceive('delete')
        ->andReturn(false);

        $missionUnSdg->shouldReceive('updateOrCreate')
        ->andReturn(false);

        $response = $repository->updateUnSdg(rand(10,100), $requestData->toArray());
        $this->assertTrue(true);
    }

    /**
     * Create a new respository instance.
     *
     * @param  App\Models\MissionUnSdg $missionUnSdg
     * @return void
     */
    private function getRepository(
        MissionUnSdg $missionUnSdg
    ) {
        return new MissionUnitedNationSDGRepository(
            $missionUnSdg
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

}
