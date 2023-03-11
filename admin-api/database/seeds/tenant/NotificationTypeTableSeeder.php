<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class NotificationTypeTableSeeder extends Seeder
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
                "notification_type" => "recommended_missions",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [                
                "notification_type" => "volunteering_hours",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [                
                "notification_type" => "volunteering_goals",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [                
                "notification_type" => "my_comments",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [                
                "notification_type" => "my_stories",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [                
                "notification_type" => "new_missions",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [                
                "notification_type" => "new_messages",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [                
                "notification_type" => "recommended_story",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [                
                "notification_type" => "mission_application",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [                
                "notification_type" => "new_news",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        ];
    
        foreach ($items as $item) {            
            \DB::table('notification_type')->insert($item);
        }
    }
}
