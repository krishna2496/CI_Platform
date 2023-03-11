<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    \DB::setDefaultConnection('tenant');
    $countryDetail = App\Models\Country::with('city')->whereNull('deleted_at')->first();
    $cityId = $countryDetail->city->first()->city_id;
    \DB::setDefaultConnection('mysql');

    return [
        'first_name' => $faker->firstname,
        'last_name' => $faker->lastname,
        'email' => $faker->unique()->email,
        'password' => str_random(10),
        'timezone_id' => 1,
        'language_id' => 1,
        'availability_id' => 1,
        'why_i_volunteer' => str_random(10),
        'employee_id' => str_random(10),
        'department' => str_random(10),
        'city_id' => $cityId,
        'country_id' => $countryDetail->country_id,
        'profile_text' => str_random(10),
        'linked_in_url' => 'https://www.'.str_random(10).'.com',
        'is_profile_complete' => '1',
        'receive_email_notification' => 1
    ];
});

$factory->define(App\Models\FooterPage::class, function (Faker\Generator $faker) {
    return [
        'slug' => str_random(20)
    ];
});


$factory->define(App\Models\UserCustomField::class, function (Faker\Generator $faker) {
    $typeArray = config('constants.custom_field_types');
    $randomTypes = array_rand($typeArray,1);
    return [
        'name' => str_random(10),
        'type' => $typeArray[$randomTypes],
        'is_mandatory' => 1,
        'translations' => [
            [
                'lang' => "en",
                'name' => str_random(10),
                'values' => "[".'1:'.rand(1, 5).",".'2:'.rand(5, 10)."]"
            ]
        ]
    ];
});

$factory->define(App\Models\Slider::class, function (Faker\Generator $faker) {
    return [
        'url' => 'https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png',
        'sort_order' => '1',
        'translations' =>  [
            [
                'lang' => 'en',
                'slider_title' => str_random(20),
                'slider_description' => str_random(200)
            ]
        ],
    ];
});

$factory->define(App\Models\Mission::class, function (Faker\Generator $faker) {
    \DB::setDefaultConnection('tenant');
    $countryDetail = App\Models\Country::with('city')->whereNull('deleted_at')->first();
    $cityId = 1;
    \DB::setDefaultConnection('mysql');

    return [
        "theme_id" => 1,
        "city_id" => $cityId,
        "country_id" => $countryDetail->country_id,
        "start_date" => "2019-05-15 10:40:00",
        "end_date" => "2022-10-15 10:40:00",
        "mission_type" => config("constants.mission_type.GOAL"),
        "publication_status" => config("constants.publication_status.APPROVED"),
        "organization_id" => 1
    ];
});

$factory->define(App\Models\Skill::class, function (Faker\Generator $faker) {
    return [
        'skill_name' => str_random(10),
        'translations' => [
            [
                'lang' => "en",
                'title' => str_random(10)
            ]
        ],
        'parent_skill' => 0,
    ];
});

$factory->define(App\Models\MissionApplication::class, function (Faker\Generator $faker) {
    return [
        'mission_id' => 1,
        'user_id' => 1,
        'availability_id' => 1,
        'applied_at' => date("Y-m-d H:i:s"),
        'approval_status' => 'AUTOMATICALLY_APPROVED',
        'motivation' => str_random(10)
    ];
});

$factory->define(App\Models\PolicyPage::class, function (Faker\Generator $faker) {
    return [
        'slug' => str_random(20)
    ];
});

$factory->define(App\Models\TenantSetting::class, function (Faker\Generator $faker) {
    return [
        'setting_id' => 2
    ];
});

$factory->define(App\Models\TenantActivatedSetting::class, function (Faker\Generator $faker) {
    return [
        'tenant_setting_id' => 114
    ];
});

$factory->define(App\Models\TimesheetDocument::class, function (Faker\Generator $faker) {
    return [
        'document_name' => 'volunteer9.png',
        'document_path' => 'https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png',
        'document_type' => 'png'
    ];
});

$factory->define(App\Models\Timesheet::class, function (Faker\Generator $faker) {
    return [
        'user_id' => 1,
        'mission_id' => 1,
        'time' => '01:01:01',
        'action' => null,
        'date_volunteered' => '2020-06-06',
        'day_volunteered' => 'WORKDAY',
        'notes' => 'Some Sample Notes',
        'status' => 'APPROVED'
    ];
});

$factory->define(App\Models\UserSkill::class, function (Faker\Generator $faker) {
    return [
        'user_id' => 1,
        'skill_id' => 1
    ];
});

$factory->define(App\Models\City::class, function (Faker\Generator $faker) {
    return [
        'country_id' => 1,
        'name' => 'test'
    ];
});

$factory->define(App\Models\UserNotification::class, function (Faker\Generator $faker) {
    return [
        'user_id' => 1,
        'notification_type_id' => 1
    ];
});

$factory->define(App\Models\MissionLanguage::class, function (Faker\Generator $faker) {
    return [
        'mission_id' => 1,
        'language_id' => 1,
        'title' => 'mission title',
        'short_description' => str_random(10),
        'objective' => str_random(10),
        'description' => [
            [
                'title' => str_random(10),
                'description' => str_random(100)
            ]
        ]
    ];
});

$factory->define(App\Models\UserFilter::class, function (Faker\Generator $faker) {
    return [
        'user_id' => 1,
        'filters' => 'a:6:{s:6:"search";s:0:"";s:10:"country_id";s:3:"233";s:7:"city_id";s:0:"";s:8:"theme_id";s:0:"";s:8:"skill_id";s:0:"";s:7:"sort_by";s:0:"";}'
    ];
});

$factory->define(App\Models\MissionSkill::class, function (Faker\Generator $faker) {
    return [
        'mission_id' => 1,
        'skill_id' => 1
    ];
});

$factory->define(App\Models\News::class, function (Faker\Generator $faker) {
    return [
        "news_image" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/unitTestFiles/sliderimg4.jpg",
        "user_name" => str_random('5'),
        "user_title" => strtoupper(str_random('3')),
        "user_thumbnail" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/unitTestFiles/sliderimg4.jpg",
        "status" => "PUBLISHED"
    ];
});

$factory->define(App\Models\NewsLanguage::class, function (Faker\Generator $faker) {
    return [
        "news_id" => null,
        "language_id" => 1,
        "title" => strtoupper(str_random('3')),
        "description" => "We have collected the following information: job title, contact information, including email address, demographic information such as zip code, preferences and interests, other information"
    ];
});

$factory->define(App\Models\NewsToCategory::class, function (Faker\Generator $faker) {
    return [
        "news_id" => 1,
        "news_category_id" => 1
    ];
});

$factory->define(App\Models\NewsCategory::class, function (Faker\Generator $faker) {
    return [
        'category_name' => str_random(10),
        'translations' =>  [
            [
                'lang' => 'en',
                'title' => str_random(20)
            ]
        ],
    ];
});

$factory->define(App\Models\Country::class, function (Faker\Generator $faker) {
    return [
        "iso"=>str_random(3)
    ];
});

$factory->define(App\Models\State::class, function (Faker\Generator $faker) {
    return [
        "country_id"=>1
    ];
});


$factory->define(App\Models\City::class, function (Faker\Generator $faker) {
    return [
        "country_id"=>1
    ];
});
$factory->define(App\Models\Organization::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(8),
        'legal_number' => rand(),
        'phone_number'=> rand(),
        'address_line_1' => str_random(6),
        'address_line_2' => str_random(6),
        'city_id' => 1,
        'state_id' => 1,
        'country_id' => 1,
        'postal_code' => rand()
    ];
});
$factory->define(App\Models\CityLanguage::class, function (Faker\Generator $faker) {
    return [
        'city_id' => 1,
        'language_id' => 1,
        'name' => $faker->name,
    ];
});

$factory->define(App\Models\CountryLanguage::class, function (Faker\Generator $faker) {
    return [
        'country_id' => 1,
        'language_id' => 1,
        'name' => $faker->name,
    ];
});

$factory->define(App\Models\DonationIpWhitelist::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'pattern' => $faker->ipv4,
        'description' => $faker->text(60)
    ];
});

$factory->define(App\Models\MissionMedia::class, function (Faker\Generator $faker) {
    return [
        'mission_id' => $faker->randomDigit
    ];
});

$factory->define(App\Models\MissionDocument::class, function (Faker\Generator $faker) {
    return [
        'mission_document_id' => $faker->randomDigit
    ];
});

$factory->define(App\Models\PaymentGateway\PaymentGatewayAccount::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'organization_id' => $faker->uuid,
        'payment_gateway_account_id' => $faker->uuid,
        'payment_gateway_type' => $faker->randomDigit
    ];
});