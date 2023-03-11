<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ActivityLogEvent extends Event
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
     * @param string  $objectClass = null,
     * @param string  $objectValue = null,
     * @param int  $objectId = null
     * @return void
     */
    public function __construct(
        $type,
        $action,
        $objectClass = null,
        $objectValue = null,
        $objectId = null
    ) {
        $this->activityDataArray['type'] = $type;
        $this->activityDataArray['action'] = $action;
        $this->activityDataArray['object_class'] = $objectClass;
        $this->activityDataArray['object_id'] = $objectId;
        $this->activityDataArray['object_value'] = $objectValue;
    }
}
