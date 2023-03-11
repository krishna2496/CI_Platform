<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StateTenantSettingSeeder extends Seeder
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
                'title' => 'state selection',
                'description' => 'State selection is enabled/disabled',
                'key' => 'state_selection',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        ];

        foreach ($items as $item) {            
            \DB::table('tenant_setting')->insert($item);
        }
    }
}
