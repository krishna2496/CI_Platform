<?php

namespace App\Exceptions;

use Exception;

class AmountException extends Exception
{
    const ERROR_MESSAGES = [
        'ERROR_AMOUNT_INVALID_VALUE' => 'Invalid value.',
        'ERROR_AMOUNT_DIVISION_BY_ZERO' => 'Division by zero.',
    ];

    public static function throw($errorCode)
    {
        throw new self(self::ERROR_MESSAGES[$errorCode]);
    }
}
