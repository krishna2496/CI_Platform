<?php

namespace Tests\Unit\Services;

use App\Models\Timesheet;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\Timesheet\TimesheetRepository;
use App\Services\TimesheetService;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Mockery;
use TestCase;

class TimesheetServiceTest extends TestCase
{
    /**
    * @testdox Test summary
    *
    * @return void
    */
    public function testSummary()
    {
        $request = new Request();
        $methodResponseFirst = $this->getMockResponseFirst();
        $methodResponseSecond = $this->getMockResponseSecond();

        $user = new User();
        $user->setAttribute('user_id', 1);
        $user->setAttribute('hours_goal', 1001);

        $timesheetRepository = $this->mock(TimesheetRepository::class);
        $timesheetRepository
            ->shouldReceive('summary')
            ->once()
            ->with($user, $request->all())
            ->andReturn($methodResponseFirst);
        $timesheetRepository
            ->shouldReceive('getUsersTotalHours')
            ->once()
            ->with(null, null)
            ->andReturn([
                [
                    'user_id' => 10,
                    'month' => 4,
                    'total_minutes' => '2815'
                ]
            ]);
        $timesheetRepository
            ->shouldReceive('findByUser')
            ->once()
            ->with($user)
            ->andReturn($methodResponseSecond);

        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);
        $tenantOptionRepository
            ->shouldReceive('getOptionValueFromOptionName')
            ->once()
            ->with('default_user_hours_goal')
            ->andReturn(null);

        $controller = $this->getController(
            $tenantOptionRepository,
            $timesheetRepository
        );

        $response = $controller->summary($user, $request->all());

        $this->assertEquals([
            'total_timesheet' => 4,
            'first_volunteered_date' => '2020-04-28',
            'total_timesheet_action' => '125',
            'total_timesheet_time' => '46:55:00',
            'total_time_seconds' => '168900',
            'total_hours_goal' => 1001,
            'total_remaining_hours' => 954.08,
            'total_completed_hours' => '46.92',
            'volunteering_rank' => 100,
            'average_volunteering_days' => 1.0
        ], $response);
    }

    private function getMockResponseFirst()
    {
        $timesheet = new Timesheet();
        $timesheet->setAttribute('total_timesheet', 4);
        $timesheet->setAttribute('first_volunteered_date', '2020-04-28');
        $timesheet->setAttribute('total_timesheet_action', '125');
        $timesheet->setAttribute('total_timesheet_time', '46:55:00');
        $timesheet->setAttribute('total_time_seconds', '168900');

        return new Collection([
            $timesheet
        ]);
    }

    private function getMockResponseSecond()
    {
        $timesheet = new Timesheet();
        $timesheet->setAttribute('date_volunteered', Carbon::createFromFormat('m-d-Y', '04-28-2020'));

        $timesheet1 = new Timesheet();
        $timesheet1->setAttribute('date_volunteered', Carbon::createFromFormat('m-d-Y', '04-29-2020'));

        $timesheet2 = new Timesheet();
        $timesheet2->setAttribute('date_volunteered', Carbon::createFromFormat('m-d-Y', '04-30-2020'));

        $timesheet3 = new Timesheet();
        $timesheet3->setAttribute('date_volunteered', Carbon::createFromFormat('m-d-Y', '05-01-2020'));

        return new Collection([
            $timesheet,
            $timesheet1,
            $timesheet2,
            $timesheet3
        ]);
    }

    /**
     * Create a new controller instance.
     *
     * @param  App\Repositories\TenantOption\TenantOptionRepository $tenantOptionRepository
     * @param  App\Repositories\Timesheet\TimesheetRepository $timesheetRepository
     *
     * @return void
     */
    private function getController(
        TenantOptionRepository $tenantOptionRepository,
        TimesheetRepository $timesheetRepository
    ) {
        return new TimesheetService(
            $timesheetRepository,
            $tenantOptionRepository
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
