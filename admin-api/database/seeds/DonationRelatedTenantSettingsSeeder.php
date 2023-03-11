<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DonationRelatedTenantSettingsSeeder extends Seeder
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
                'title' => 'EAF donation mission',
                'description' => 'Enable/disable missions of type EAF (Employee Assistance Fund)',
                'key' => 'eaf',
                'created_at' => Carbon::now()
            ],
            [
                'title' => 'Disaster relief donation mission',
                'description' => 'Enable/disable mission of type disaster relief',
                'key' => 'disaster_relief',
                'created_at' => Carbon::now()
            ],
            [
                'title' => 'Donation mission comments',
                'description' => 'Enable/disable commments on donation missions',
                'key' => 'donation_mission_comments',
                'created_at' => Carbon::now()
            ],
            [
                'title' => 'Donation mission ratings',
                'description' => 'Enable/disable ratings on donation missions',
                'key' => 'donation_mission_ratings',
                'created_at' => Carbon::now()
            ],
            [
                'title' => 'Recent donors',
                'description' => 'Display/hide list of recent donors of a mission',
                'key' => 'recent_donors',
                'created_at' => Carbon::now(),
            ]
        ];

        foreach ($items as $item) {
            DB::table('tenant_setting')->insert($item);
        }
    }
}
