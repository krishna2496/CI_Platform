<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VolunteerTenantSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $volunteeringSetting = [
            'title' => 'Volunteering',
            'description' => 'Enable/disable volunteering on the platform',
            'key' => 'volunteering',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        DB::table('tenant_setting')->insert($volunteeringSetting);
    }
}
