<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VolunteeringTimeAndGoalMissionTenantSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'title' => 'Volunteering time mission',
                'description' => 'Enable/disable volunteering time mission',
                'key' => 'volunteering_time_mission',
                'created_at' => Carbon::now()
            ],
            [
                'title' => 'Volunteering goal mission',
                'description' => 'Enable/disable volunteering goal mission',
                'key' => 'volunteering_goal_mission',
                'created_at' => Carbon::now()
            ]
        ];

        foreach ($items as $item) {
            DB::table('tenant_setting')->insert($item);
        }
    }
}
