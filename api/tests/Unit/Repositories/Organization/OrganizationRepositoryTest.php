<?php

namespace Tests\Unit\Repositories\Organization;

use App\Models\Mission;
use App\Models\Organization;
use App\Repositories\Organization\OrganizationRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use TestCase;

class OrganizationRepositoryTest extends TestCase
{
    /**
     * Test getOrganizationDetails method
     *
     * @return void
     */
    public function testGetOrganizationDetails()
    {
        $organizationInfo = factory(Organization::class)->make([
            'organization_id' => 'organizationID'
        ]);

        $organization = $this->mock(Organization::class);
        $organization->shouldReceive('with')
            ->once()
            ->with('paymentGatewayAccount')
            ->andReturnSelf()
            ->shouldReceive('findOrfail')
            ->once()
            ->with($organizationInfo->organization_id)
            ->andReturn($organizationInfo);

        $mission = $this->mock(Mission::class);

        $response = $this->getRepository(
            $organization,
            $mission
        )->getOrganizationDetails($organizationInfo->organization_id);

        $this->assertSame($response, $organizationInfo);
    }

    /**
     * Test getOrganizationList method
     *
     * @return void
     */
    public function testGetOrganizationList()
    {
        $request = new Request();
        $request->query->add([
            'perPage' => 1
        ]);

        $organizationInfo = factory(Organization::class, 2)->make();
        $paginator = $this->getPaginator(
            $organizationInfo,
            $organizationInfo->count(),
            $request->get('perPage')
        );

        $organization = $this->mock(Organization::class);
        $organization->shouldReceive('with')
            ->once()
            ->with('paymentGatewayAccount')
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->once()
            ->with($request->get('perPage'))
            ->andReturn($paginator);

        $mission = $this->mock(Mission::class);

        $response = $this->getRepository(
            $organization,
            $mission
        )->getOrganizationList($request);

        $this->assertSame($response, $paginator);
    }

    /**
     * Test isLinkedToMissionWithDonationAttribute method then return true
     *
     * @return void
     */
    public function testIsLinkedToMissionWithDonationAttributeTrue()
    {
        $organizationID = 'OrganizationID';
        $organization = $this->mock(Organization::class);
        $mission = $this->mock(Mission::class);

        $mission
            ->shouldReceive('join')
            ->with('donation_attribute', 'donation_attribute.mission_id', '=', 'mission.mission_id')
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('mission.organization_id', $organizationID)
            ->andReturnSelf()
            ->shouldReceive('count')
            ->andReturn(1);

        $response = $this->getRepository(
            $organization,
            $mission
        )->isLinkedToMissionWithDonationAttribute($organizationID);

        $this->assertTrue($response);
    }

    /**
     * Test isLinkedToMissionWithDonationAttribute method then return false
     *
     * @return void
     */
    public function testIsLinkedToMissionWithDonationAttributeFalse()
    {
        $organizationID = 'OrganizationID';
        $organization = $this->mock(Organization::class);
        $mission = $this->mock(Mission::class);

        $mission
            ->shouldReceive('join')
            ->with('donation_attribute', 'donation_attribute.mission_id', '=', 'mission.mission_id')
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('mission.organization_id', $organizationID)
            ->andReturnSelf()
            ->shouldReceive('count')
            ->andReturn(0);

        $response = $this->getRepository(
            $organization,
            $mission
        )->isLinkedToMissionWithDonationAttribute($organizationID);

        $this->assertFalse($response);
    }

    /**
     * Create a new repository instance.
     *
     * @param Organization $model
     * @param Mission $model
     *
     * @return void
     */
    private function getRepository(
        Organization $organization,
        Mission $mission
    ) {
        return new OrganizationRepository(
            $organization,
            $mission
        );
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
}
