<?php

namespace Tests\Unit\Repositories\Timesheet;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\S3Helper;
use App\Models\Mission;
use App\Models\MissionLanguage;
use App\Models\Timesheet;
use App\Models\TimesheetDocument;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\Timesheet\TimesheetRepository;
use App\Repositories\User\UserRepository;
use Bschmitt\Amqp\Amqp;
use TestCase;
use Mockery;
use DB;
use Illuminate\Database\Eloquent\Collection;

class TimeSheetRepositoryTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->generateMocks();
    }
    /**
    * @testdox Get sum of all users approved time
    */
    public function testGetSumOfUsersTotalMinutes()
    {
        $userTotalMinutes = 100;
        $timeSheet = $this->mock(Timesheet::class);
        $totalMinutes = new Collection([
            [
                'user_id' => 1,
                'month' => 9,
                'total_minutes' => $userTotalMinutes
            ]
        ]);

        DB::shouldReceive('raw')
            ->with("user_id, MONTH(date_volunteered) as month,
        sum(((hour(time) * 60) + minute(time))) as 'total_minutes'")
            ->once()
            ->andReturn($totalMinutes);

        $timeSheet
            ->shouldReceive('select')
            ->once()
            ->with($totalMinutes)
            ->andReturnSelf();

        $timeSheet
            ->shouldReceive('whereHas')
            ->once()
            ->with('mission')
            ->andReturnSelf();

        $timeSheet
            ->shouldReceive('leftjoin')
            ->once()
            ->with('mission', 'timesheet.mission_id', '=', 'mission.mission_id')
            ->andReturnSelf();

        $timeSheet
            ->shouldReceive('where')
            ->once()
            ->with('mission.publication_status', config("constants.publication_status")["APPROVED"])
            ->andReturnSelf();

        $statusArray = [
            config('constants.timesheet_status.APPROVED'),
            config('constants.timesheet_status.AUTOMATICALLY_APPROVED')
        ];

        $timeSheet
            ->shouldReceive('whereIn')
            ->once()
            ->with('status', $statusArray)
            ->andReturnSelf();

        $timeSheet
            ->shouldReceive('get')
            ->once()
            ->andReturnSelf();

        $timeSheet
            ->shouldReceive('first')
            ->once()
            ->andReturnSelf();

        $timeSheet
            ->shouldReceive('toArray')
            ->once()
            ->andReturn($totalMinutes[0]);

        $instance = $this->getInstance($timeSheet);
        $originalTotalMinutes = $instance->getSumOfUsersTotalMinutes();

        $this->assertSame($originalTotalMinutes, $userTotalMinutes);
    }

    private function getInstance($timeSheet)
    {
        return new TimesheetRepository(
            $timeSheet,
            $this->mission,
            $this->missionLanguage,
            $this->timesheetDocument,
            $this->helpers,
            $this->languageHelper,
            $this->s3Helper,
            $this->tenantOptionRepository,
            $this->userRepository,
            $this->amqp
        );
    }

    private function generateMocks()
    {
        $this->mission = $this->mock(Mission::class);
        $this->missionLanguage = $this->mock(MissionLanguage::class);
        $this->timesheetDocument = $this->mock(TimesheetDocument::class);
        $this->helpers = $this->mock(Helpers::class);
        $this->languageHelper = $this->mock(LanguageHelper::class);
        $this->s3Helper = $this->mock(S3Helper::class);
        $this->tenantOptionRepository = $this->mock(TenantOptionRepository::class);
        $this->userRepository = $this->mock(UserRepository::class);
        $this->amqp = $this->mock(Amqp::class);
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
