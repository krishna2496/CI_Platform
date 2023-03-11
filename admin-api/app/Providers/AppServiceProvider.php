<?php
namespace App\Providers;

use App\Rules\CustomValidationRules;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        CustomValidationRules::validate();
    }
    
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
