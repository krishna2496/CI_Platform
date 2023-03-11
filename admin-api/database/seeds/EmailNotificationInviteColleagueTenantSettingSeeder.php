<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EmailNotificationInviteColleagueTenantSettingSeeder extends Seeder
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
                'title' => 'Email notification for invite colleague',
                'description' => 'Enable/disable email notification for invite colleague',
                'key' => 'email_notification_invite_colleague',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        ];

        foreach ($items as $item) {            
            \DB::table('tenant_setting')->insert($item);
        }        
    }
}
