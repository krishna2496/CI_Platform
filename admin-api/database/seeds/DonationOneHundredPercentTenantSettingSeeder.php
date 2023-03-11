<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DonationOneHundredPercentTenantSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $item = [
            'title' => '100% Donation',
            'description' => 'Enable/disable 100% donation',
            'key' => '100_percent_donation',
            'created_at' => Carbon::now()
        ];

        DB::table('tenant_setting')->insert($item);
    }
}