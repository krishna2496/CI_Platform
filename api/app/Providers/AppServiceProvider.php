<?php
namespace App\Providers;

use App\Rules\CustomValidationRules;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        CustomValidationRules::validate();
        if (in_array(env('APP_ENV'), ['staging', 'production'])) {
            URL::forceScheme('https');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191); //NEW: Increase StringLength
    }
    
}
