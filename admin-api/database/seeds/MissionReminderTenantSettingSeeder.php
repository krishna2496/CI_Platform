<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MissionReminderTenantSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $item = [
            'title' => 'Mission reminder',
            'description' => 'Enable/disable mission reminder on the platform',
            'key' => 'mission_reminder',
            'created_at' => Carbon::now()
        ];

        DB::table('tenant_setting')->insert($item);
    }
}
