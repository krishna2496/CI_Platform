<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MissionImpactTenantSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $item = [
            'title' => 'Mission impact',
            'description' => 'Enable/disable mission impact on the platform',
            'key' => 'mission_impact',
            'created_at' => Carbon::now()
        ];

        DB::table('tenant_setting')->insert($item);
    }
}
