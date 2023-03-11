<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ImpactDonationTenantSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $volunteeringSetting = [
            'title' => 'Impact donation',
            'description' => 'Enable/disable impact donation on the platform',
            'key' => 'impact_donation',
            'created_at' => Carbon::now()
        ];

        DB::table('tenant_setting')->insert($volunteeringSetting);
    }
}
