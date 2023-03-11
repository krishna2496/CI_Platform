<?php

namespace Tests\Unit\Repositories\DonationIp;

use App\Helpers\S3Helper;
use App\Models\MissionMedia;
use App\Repositories\MissionMedia\MissionMediaRepository;
use Mockery;
use TestCase;

class MissionMediaRepositoryTest extends TestCase
{

    /**
     * @testdox Test saveMediaImages with default media value
     */
    public function testSaveMediaImagesDefault()
    {
        $mediaImages = [
            [
                'default' => '1',
                'sort_order' => 0,
                'internal_note' => 'internalInfo',
                'media_path' => 'mediaPath'
            ]
        ];
        $missionId = 1;
        $tenantName = 'tenantName';
        $responsePath = 'https:://s3ResponsePath.url/sample.jpg';
        $missionMedia = [
            'mission_id' => $missionId,
            'default' => $mediaImages[0]['default'],
            'sort_order' => $mediaImages[0]['sort_order'],
            'internal_note' => $mediaImages[0]['internal_note']
        ];

        $model = $this->mock(MissionMedia::class);
        $model->shouldReceive('where')
            ->once()
            ->with('mission_id', $missionId)
            ->andReturn($model)
            ->shouldReceive('update')
            ->once()
            ->with([
                'default' => '0'
            ])
            ->andReturn(true);

        $mission = factory(MissionMedia::class)->make([
            'mission_media_id' => 1,
            'mission_id' => $missionId
        ]);

        $model->shouldReceive('create')
            ->once()
            ->with($missionMedia)
            ->andReturn($mission);

        $s3helper = $this->mock(S3Helper::class);
        $s3helper->shouldReceive('uploadFileOnS3Bucket')
            ->once()
            ->with(
                $mediaImages[0]['media_path'],
                $tenantName,
                "missions/$missionId/media/$mission->mission_media_id"
            )
            ->andReturn($responsePath);

        $response = $this->getRepository(
            $model,
            $s3helper
        )->saveMediaImages(
            $mediaImages,
            $tenantName,
            $missionId
        );

        $this->assertNull($response);
    }

    /**
     * @testdox Test saveMediaImages with not default media value
     */
    public function testSaveMediaImagesNotDefault()
    {
        $mediaImages = [
            [
                'default' => 0,
                'sort_order' => 0,
                'internal_note' => 'internalInfo',
                'media_path' => 'mediaPath'
            ]
        ];
        $missionId = 1;
        $tenantName = 'tenantName';
        $responsePath = 'https:://s3ResponsePath.url/sample.jpg';
        $missionMedia = [
            'mission_id' => $missionId,
            'default' => $mediaImages[0]['default'],
            'sort_order' => $mediaImages[0]['sort_order'],
            'internal_note' => $mediaImages[0]['internal_note']
        ];

        $model = $this->mock(MissionMedia::class);
        $model->shouldReceive('where')
            ->never()
            ->shouldReceive('update')
            ->never();

        $mission = factory(MissionMedia::class)->make([
            'mission_media_id' => 1,
            'mission_id' => $missionId
        ]);

        $model->shouldReceive('create')
            ->once()
            ->with($missionMedia)
            ->andReturn($mission);

        $s3helper = $this->mock(S3Helper::class);
        $s3helper->shouldReceive('uploadFileOnS3Bucket')
            ->once()
            ->with(
                $mediaImages[0]['media_path'],
                $tenantName,
                "missions/$missionId/media/$mission->mission_media_id"
            )
            ->andReturn($responsePath);

        $model->shouldReceive('where')
            ->once()
            ->with([
                ['mission_id', '=', $missionId],
                ['media_type', '<>', 'mp4']
            ])
            ->andReturn($model)
            ->shouldReceive('orderBy')
            ->once()
            ->with('sort_order', 'ASC')
            ->andReturn($model)
            ->shouldReceive('first')
            ->once()
            ->andReturn($mission);

        $model->shouldReceive('where')
            ->once()
            ->with('mission_media_id', $mission->mission_media_id)
            ->andReturn($model)
            ->shouldReceive('update')
            ->once()
            ->with([
                'default' => '1'
            ])
            ->andReturn(true);

        $response = $this->getRepository(
            $model,
            $s3helper
        )->saveMediaImages(
            $mediaImages,
            $tenantName,
            $missionId
        );

        $this->assertNull($response);
    }

    /**
     * Create a new repository instance.
     *
     * @param  MissionMedia $model
     * @param  S3Helper $s3helper
     *
     * @return void
     */
    private function getRepository(
        MissionMedia $model,
        S3Helper $s3helper
    ) {
        return new MissionMediaRepository(
            $model,
            $s3helper
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