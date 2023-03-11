<?php

namespace App\Events\Mission;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class MissionDeletedEvent extends Event
{
    use SerializesModels;

    /**
     * @var int
     */
    public $missionId;

    public function __construct(int $missionId)
    {
        $this->missionId = $missionId;
    }
}
