<?php

namespace App\Events\User;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class UserActivityLogEvent extends Event
{
    use SerializesModels;

    /**
     * @var array
     */
    public $activityDataArray;


    /**
     * Create a new event instance.
     *
     * @param string $type
     * @param string $action
     * @param string $userType
     * @param string $userValue
     * @param string  $objectClass = null,
     * @param string  $objectValue = null,
     * @param int  $userId = null,
     * @param int  $objectId = null
     * @return void
     */
    public function __construct(
        $type,
        $action,
        $userType,
        $userValue,
        $objectClass = null,
        $objectValue = null,
        $userId = null,
        $objectId = null
    ) {
        $this->activityDataArray['type'] = $type;
        $this->activityDataArray['action'] = $action;
        $this->activityDataArray['user_type'] = $userType;
        $this->activityDataArray['user_value'] = $userValue;
        $this->activityDataArray['object_class'] = $objectClass;
        $this->activityDataArray['object_id'] = $objectId;
        $this->activityDataArray['object_value'] = $objectValue;
        $this->activityDataArray['user_id'] = $userId;
    }
}
