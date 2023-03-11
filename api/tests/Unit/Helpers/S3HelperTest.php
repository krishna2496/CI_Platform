<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ResponseHelper;
use App\Helpers\S3Helper;
use Illuminate\Support\Facades\Storage;
use Mockery;
use TestCase;
use League\Flysystem\Filesystem;

class S3HelperTest extends TestCase
{
    /**
     * @var S3Helper
     */
    private $s3Helper;

    /**
     * Start up the class and data neede for the class to tests
     */
    protected function setUp(): void
    {
        parent::setUp();
        $responseHelper = $this->mock(ResponseHelper::class);
        $this->s3Helper = new S3Helper($responseHelper);
    }

    /**
     * @testdox Test uploadFileOnS3Bucket method on S3Helper Class
     */
    public function testUploadFileOnS3Bucket()
    {
        $url = 'http://admin-m7pww5ymmj28.back.staging.optimy.net/assets/images/optimy-logo.png';
        $tenantName = 'testTenant';
        $customPath = 'custom/path';

        $path = [
            $tenantName,
            $customPath,
            'optimy-logo.png'
        ];
        $path = implode('/', $path);

        $context = stream_context_create(array('http'=> array(
            'timeout' => 1200
        )));

        $fileSystem = $this->mock(Filesystem::class);
        $fileSystem->shouldReceive('put')
            ->once()
            ->with(
                $path,
                file_get_contents($url, false, $context)
            )
            ->andReturn(true);

        Storage::shouldReceive('disk')
            ->once()
            ->with('s3')
            ->andReturn($fileSystem);;

        $result = $this->s3Helper->uploadFileOnS3Bucket(
            $url,
            $tenantName,
            $customPath
        );

        $expected = 'https://'
            .env('AWS_S3_BUCKET_NAME')
            .'.s3.'
            .env('AWS_REGION')
            .'.amazonaws.com/'
            .$path;
        $this->assertSame($result, $expected);
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