<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RatingForVolunteersTenantSettingSeeder extends Seeder
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
                'title' => 'Enable/disable mission ratings for volunteers',
                'description' => 'Enable/disable mission ratings so only volunteers that have been approved to the mission can rate the mission',
                'key' => 'mission_rating_volunteer',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        ];

        foreach ($items as $item) {            
            \DB::table('tenant_setting')->insert($item);
        }
    }
}
