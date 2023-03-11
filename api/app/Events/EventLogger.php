<?php

namespace App\Events;

use App\Events\User\UserActivityLogEvent;
use App\Models\TenantOption;

class EventLogger
{
    static function logCustomStyleUpdate(string $apiUser, array $context)
    {
        $customScssFileKey = 'custom_scss_file';
        if (array_key_exists($customScssFileKey, $context) && $context[$customScssFileKey]->isFile()) {
            $context[$customScssFileKey] = $context[$customScssFileKey]->getClientOriginalName();
        }

        event(new UserActivityLogEvent(
            config('constants.activity_log_types.STYLE'),
            config('constants.activity_log_actions.UPDATED'),
            config('constants.activity_log_user_types.API'),
            $apiUser,
            TenantOption::class,
            $context,
            null,
            null
        ));
    }
}
