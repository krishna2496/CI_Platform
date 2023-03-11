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
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});

$factory->define(App\Models\Tenant::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(10),
        'sponsor_id' => rand(100, 5000)
    ];
});

$factory->define(App\Models\TenantCurrency::class, function (Faker\Generator $faker, $tenant) {
    
    $code = $faker->unique()->regexify("/^[A-Z]{3}$");
    return [
        'code' => $code,
        'tenant_id' => $tenant['tenant_id'],
        'default' => 1,
        'is_active' => 1,
    ];
});
