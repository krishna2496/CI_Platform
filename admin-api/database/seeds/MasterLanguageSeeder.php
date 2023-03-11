<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MasterLanguageSeeder extends Seeder
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
                ['code' => 'en'],
                ['name' => 'English', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'fr'],
                ['name' => 'Français', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'de'],
                ['name' => 'Deutsch', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'it'],
                ['name' => 'Italiano', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'es'],
                ['name' => 'Español', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'pt'],
                ['name' => 'Português', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'nl'],
                ['name' => 'Nederlands', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'pl'],
                ['name' => 'Polski', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'ru'],
                ['name' => 'Pусский', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'fi'],
                ['name' => 'Suomeksi', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ],
            [
                ['code' => 'zh'],
                ['name' => '中文', 'status' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ]
        ];

        foreach ($items as $item) {
            \DB::table('language')->updateOrInsert($item[0], $item[1]);
        }
    }
}
