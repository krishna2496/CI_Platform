<?php

namespace Tests\Unit\Repositories\DonationIp;

use App\Models\DonationIpWhitelist;
use App\Repositories\DonationIp\WhitelistRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Illuminate\Pagination\LengthAwarePaginator;
use TestCase;

class WhitelistRepositoryTest extends TestCase
{
    /**
     * Test findById
     *
     * @return void
     */
    public function testFindById()
    {
        $modelData = factory(DonationIpWhitelist::class)->make();

        $model = $this->mock(DonationIpWhitelist::class);
        $model->shouldReceive('findOrFail')
            ->once()
            ->with($modelData->id)
            ->andReturn($modelData);

        $response = $this->getRepository(
            $model
        )->findById($modelData->id);

        $this->assertSame($modelData, $response);
    }

    /**
     * Test findById with exception
     *
     * @return void
     */
    public function testFindByIdException()
    {
        $this->expectException(ModelNotFoundException::class);
        $modelData = factory(DonationIpWhitelist::class)->make();

        $model = $this->mock(DonationIpWhitelist::class);
        $model->shouldReceive('findOrFail')
            ->once()
            ->with($modelData->id)
            ->andThrow(new ModelNotFoundException);

        $response = $this->getRepository(
            $model
        )->findById($modelData->id);
    }

    /**
     * Test getList with all record limit to 10
     *
     * @return void
     */
    public function testGetList()
    {
        $paginate = [
            'perPage' => 10
        ];
        $filters = [
            'search' => 'search',
            'order' => [
                'pattern' => null,
                'created_at' => 'desc'
            ]
        ];

        $modelData = factory(DonationIpWhitelist::class, 2)->make();
        $paginator = $this->getPaginator(
            $modelData,
            $modelData->count(),
            $paginate['perPage']
        );

        $model = $this->mock(DonationIpWhitelist::class);
        $model
            ->shouldReceive('select')
            ->once()
            ->with(
                'id',
                'pattern',
                'description',
                'created_at'
            )
            ->andReturn($model);

        $model
            ->shouldReceive('when')
            ->twice()
            ->with(
                Mockery::anyOf(
                    $filters['search'],
                    $filters['order']
                ),
                Mockery::any()
            )
            ->andReturn($model);

        $model->shouldReceive('paginate')
            ->once()
            ->with($paginate['perPage'])
            ->andReturn($paginator);

        $response = $this->getRepository(
            $model
        )->getList($paginate, $filters);

        $this->assertSame($paginator, $response);
    }

    /**
     * Test getList without pagination (all results)
     *
     * @return void
     */
    public function testGetListWithoutPagination()
    {
        $paginate = [
            'perPage' => null
        ];
        $filters = [
            'search' => 'search',
            'order' => [
                'pattern' => null,
                'created_at' => 'desc'
            ]
        ];

        $modelData = factory(DonationIpWhitelist::class, 2)->make();
        $model = $this->mock(DonationIpWhitelist::class);
        $model
            ->shouldReceive('select')
            ->once()
            ->with(
                'id',
                'pattern',
                'description',
                'created_at'
            )
            ->andReturn($model);

        $model
            ->shouldReceive('when')
            ->twice()
            ->with(
                Mockery::anyOf(
                    $filters['search'],
                    $filters['order']
                ),
                Mockery::any()
            )
            ->andReturn($model);

        $model->shouldReceive('paginate')
            ->never();

        $model->shouldReceive('get')
            ->once()
            ->andReturn($modelData);

        $response = $this->getRepository(
            $model
        )->getList($paginate, $filters);

        $this->assertSame($modelData, $response);
    }

    /**
     * Test Store method
     *
     * @return void
     */
    public function testCreate()
    {
        $modelData = new DonationIpWhitelist();
        $modelData->setAttribute('pattern', '192.168.0.0/26')
            ->setAttribute('description', 'IDR IP');
        $model = $this->mock(DonationIpWhitelist::class);
        $model->shouldReceive('create')
            ->once()
            ->with($modelData->toArray())
            ->andReturn($modelData);

        $response = $this->getRepository(
            $model
        )->create($modelData);

        $this->assertSame($modelData, $response);
    }

    /**
     * Test updated method
     *
     * @return void
     */
    public function testUpdate()
    {
        $modelData = factory(DonationIpWhitelist::class)->make();
        $payload = [
            'pattern' => $modelData->pattern,
            'description' => $modelData->description
        ];

        $model = $this->mock(DonationIpWhitelist::class);
        $model->shouldReceive('find')
            ->once()
            ->with($modelData->id)
            ->andReturn($model);

        $model->shouldReceive('update')
            ->once()
            ->with($payload)
            ->andReturn(true);

        $response = $this->getRepository(
            $model
        )->update($modelData);

        $this->assertTrue($response);
    }

    /**
     * Test updated method
     *
     * @return void
     */
    public function testDelete()
    {
        $modelData = factory(DonationIpWhitelist::class)->make();

        $model = $this->mock(DonationIpWhitelist::class);
        $model->shouldReceive('find')
            ->once()
            ->with($modelData->id)
            ->andReturn($model);

        $model->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $response = $this->getRepository(
            $model
        )->delete($modelData->id);

        $this->assertTrue($response);
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
     * Create a new repository instance.
     *
     * @param  DonationIpWhitelist $model
     *
     * @return void
     */
    private function getRepository(
        DonationIpWhitelist $model
    ) {
        return new WhitelistRepository(
            $model
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