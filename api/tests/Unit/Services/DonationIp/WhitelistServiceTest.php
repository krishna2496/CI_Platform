<?php
    
namespace Tests\Unit\Services\DoantionIp;

use App\Models\DonationIpWhitelist;
use App\Repositories\DonationIp\WhitelistRepository;
use App\Services\DonationIp\WhitelistService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use TestCase;

class WhitelistServiceTest extends TestCase
{
    /**
     * Test findById
     *
     * @return void
     */
    public function testFindById()
    {
        $whitelisted = $this->whitelisted();

        $whitelistRepository = $this->mock(WhitelistRepository::class);
        $whitelistRepository->shouldReceive('findById')
            ->once()
            ->with($whitelisted->id)
            ->andReturn($whitelisted);

        $response = $this->getService(
            $whitelistRepository
        )->findById($whitelisted->id);

        $this->assertSame($whitelisted, $response);
    }

    /**
     * Test getList
     *
     * @return void
     */
    public function testGetList()
    {
        $whitelisted = $this->whitelisted();
        $paginate = [];
        $filters = [];
        $whitelistRepository = $this->mock(WhitelistRepository::class);
        $whitelistRepository->shouldReceive('getList')
            ->once()
            ->with($paginate, $filters)
            ->andReturn([$whitelisted]);

        $response = $this->getService(
            $whitelistRepository
        )->getList($paginate, $filters);

        $this->assertSame([$whitelisted], $response);
    }

    /**
     * Test create
     *
     * @return void
     */
    public function testCreate()
    {
        $whitelisted = new DonationIpWhitelist();
        $whitelisted->setAttribute('pattern', '192.168.0.0/26')
            ->setAttribute('description', 'IDR IP');
        $payload = [
            'pattern' => $whitelisted->pattern,
            'description' => $whitelisted->description
        ];
        $whitelistRepository = $this->mock(WhitelistRepository::class);
        $whitelistRepository->shouldReceive('create')
            ->once()
            ->with($whitelisted)
            ->andReturn($whitelisted);

        $response = $this->getService(
            $whitelistRepository
        )->create($whitelisted);

        $this->assertSame($whitelisted, $response);
    }

    /**
     * Test create
     *
     * @return void
     */
    public function testUpdate()
    {
        $whitelisted = $this->whitelisted();
        $whitelistRepository = $this->mock(WhitelistRepository::class);
        $whitelistRepository->shouldReceive('update')
            ->once()
            ->with($whitelisted)
            ->andReturn($whitelisted);

        $response = $this->getService(
            $whitelistRepository
        )->update($whitelisted);

        $this->assertSame($whitelisted, $response);
    }

    /**
     * Test create
     *
     * @return void
     */
    public function testDelete()
    {
        $whitelisted = $this->whitelisted();
        $whitelistRepository = $this->mock(WhitelistRepository::class);
        $whitelistRepository->shouldReceive('delete')
            ->once()
            ->with($whitelisted->id)
            ->andReturn(true);

        $response = $this->getService(
            $whitelistRepository
        )->delete($whitelisted->id);

        $this->assertTrue($response);
    }

    /**
     * Returns sample of custom fields
     *
     * @return array
     */
    private function whitelisted()
    {
        return factory(DonationIpWhitelist::class)->make();
    }

    /**
     * Create a new service instance.
     *
     * @param  WhitelistRepository $whitelistRepository
     *
     * @return void
     */
    private function getService(WhitelistRepository $whitelistRepository)
    {
        return new WhitelistService(
            $whitelistRepository
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
