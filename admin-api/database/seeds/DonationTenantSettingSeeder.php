<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DonationTenantSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $donationSetting = [
            'title' => 'Donation',
            'description' => 'Enable/disable donation on the platform',
            'key' => 'donation',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        DB::table('tenant_setting')->insert($donationSetting);
    }
}
