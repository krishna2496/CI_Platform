<?php
namespace App\Exceptions;

use Exception;

class SamlException extends Exception
{
    public static function throw($errorCode)
    {
        throw new self(
            trans('messages.custom_error_message.'.$errorCode),
            config('constants.error_codes.'.$errorCode)
        );
    }
}
