<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MessagingTenantSettingSeeder extends Seeder
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
                'title' => 'Enable/disable messaging on platform',
                'description' => 'Enable/disable message pages on platform',
                'key' => 'message_enabled',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        ];

        foreach ($items as $item) {            
            \DB::table('tenant_setting')->insert($item);
        }
    }
}
