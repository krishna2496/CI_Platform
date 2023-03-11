<?php
namespace Tests\Unit\Http\Controllers\App\Timesheet;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\Timesheet\TimesheetController;
use App\Repositories\Timesheet\TimesheetRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TestCase;

class TimesheetControllerTest extends TestCase
{
    public function testGetSumOfUsersTotalMinutes()
    {
        $timesheetRepositoryMock = $this->getTimesheetRepositoryMock();
        $timesheetRepositoryMock->expects($this->once())
            ->method('getSumOfUsersTotalMinutes')
            ->willReturn(1825);

        $instance = $this->getInstance(['timesheetRepository' => $timesheetRepositoryMock]);

        $response = $instance->getSumOfUsersTotalMinutes($timesheetRepositoryMock);
        $resultData = $response->getData();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($resultData->data->total_minutes, 1825);
    }

    private function getInstance($defaults = [])
    {
        $timesheetRepository = array_key_exists('timesheetRepository', $defaults) ?
            $defaults['timesheetRepository'] : $this->getTimesheetRepositoryMock();
            

        return new TimesheetController(
            $this->getUserRepositoryMock(),
            $timesheetRepository,
            new ResponseHelper(),
            new Request()
        );
    }

    private function getUserRepositoryMock()
    {
        return $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTimesheetRepositoryMock()
    {
        return $this->getMockBuilder(TimesheetRepository::class)
            ->setMethods(['getSumOfUsersTotalMinutes'])
            ->disableOriginalConstructor()
            ->getMock();
    }
}
