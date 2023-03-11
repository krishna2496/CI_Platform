<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DonationIpWhitelistTenantSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $item = [
            'title' => 'Donation IP whitelisting',
            'description' => 'Enable/disable donation IP whitelisting',
            'key' => 'donation_ip_whitelist',
            'created_at' => Carbon::now()
        ];

        DB::table('tenant_setting')->insert($item);
    }
}