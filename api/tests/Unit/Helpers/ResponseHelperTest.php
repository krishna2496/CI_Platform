<?php
namespace Tests\Unit\Helpers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use TestCase;

class ResponseHelperTest extends TestCase
{
    
    public function testSuccess()
    {
        $responseHelper = new ResponseHelper();
        $apiStatus = '200';
        $apiMessage = 'Success';
        $apiData = [
            'title' => 'Sample title',
            'description' => '012345'
        ];

        $response = $responseHelper->success($apiStatus, $apiMessage, $apiData);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($response->getStatusCode(), 200);
        $data = json_decode($response->getContent(), true)['data'];
        $this->assertSame($data, [
            'title' => 'Sample title',
            'description' => 12345 // because of JSON_NUMERIC_CHECK option
        ]);
    }

    public function testSuccessWithoutNumericConversion()
    {
        $responseHelper = new ResponseHelper();
        $apiStatus = '200';
        $apiMessage = 'Success';
        $apiData = [
            'title' => 'Sample title',
            'description' => '012345'
        ];

        $response = $responseHelper->success($apiStatus, $apiMessage, $apiData, false);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($response->getStatusCode(), 200);
        $data = json_decode($response->getContent(), true)['data'];
        $this->assertSame($data, [
            'title' => 'Sample title',
            'description' => '012345' // numeric strings doesn't get converted to int
        ]);
    }

    public function testSuccessWithPagination()
    {
        $responseHelper = new ResponseHelper();
        $apiStatus = '200';
        $apiMessage = 'Success';
        $items = [
            [
                'title' => 'Sample title',
                'description' => '012345'
            ],
            [
                'title' => 'Sample title 2',
                'description' => '012345'
            ],
        ];
        $apiData = new LengthAwarePaginator($items, count($items), 1);

        $response = $responseHelper->successWithPagination($apiStatus, $apiMessage, $apiData);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($response->getStatusCode(), 200);
        $data = json_decode($response->getContent(), true)['data'];
        $this->assertSame($data, [
            [
                'title' => 'Sample title',
                'description' => 12345
            ],
            [
                'title' => 'Sample title 2',
                'description' => 12345
            ],
        ]);
    }

    public function testError()
    {
        $responseHelper = new ResponseHelper();
        $statusCode = 400;
        $statusType = 'Bad Request';
        $customErrorMessage = 'This is a bad request';

        $response = $responseHelper->error($statusCode, $statusType, $statusCode, $customErrorMessage);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($response->getStatusCode(), 400);
        $errors = json_decode($response->getContent(), true)['errors'][0];
        $this->assertSame($errors, [
            'status' => $statusCode,
            'type' => $statusType,
            'code' => $statusCode,
            'message' => $customErrorMessage
        ]);
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