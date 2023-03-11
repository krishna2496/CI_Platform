<?php
namespace App\Rules;

use Illuminate\Support\Facades\Validator;

class CustomValidationRules
{
    public static function validate()
    {
        Validator::extend('valid_fqdn', function ($attribute, $value) {
            if (filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                return true;
            }
            return false;
        });
    }
}
