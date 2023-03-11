<?php
namespace App\Repositories\MissionInvite;

use App\Repositories\MissionInvite\MissionInviteInterface;
use App\Helpers\ResponseHelper;
use App\Models\MissionInvite;
use Illuminate\Support\Collection;

class MissionInviteRepository implements MissionInviteInterface
{
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Models\MissionInvite
     */
    public $missionInvite;
    
    /**
     * Create a new MissionInvite repository instance.
     *
     * @param  Illuminate\Http\ResponseHelper $responseHelper
     * @param  App\Models\MissionInvite $missionInvite
     * @return void
     */
    public function __construct(
        ResponseHelper $responseHelper,
        MissionInvite $missionInvite
    ) {
        $this->responseHelper = $responseHelper;
        $this->missionInvite = $missionInvite;
    }

    /**
     * Check user is already invited for a mission
     *
     * @param int $missionId
     * @param int $inviteUserId
     * @param int $fromUserId
     * @return Illuminate\Support\Collection
     */
    public function getInviteMission(int $missionId, int $inviteUserId, int $fromUserId): Collection
    {
        return $this->missionInvite->getMissionInvite($missionId, $inviteUserId, $fromUserId);
    }
    
    /**
     * Store a newly created resource into database
     *
     * @param int $missionId
     * @param int $inviteUserId
     * @param int $fromUserId
     * @return App\Models\MissionInvite
     */
    public function inviteMission(int $missionId, int $inviteUserId, int $fromUserId): MissionInvite
    {
        return $this->missionInvite
        ->create(['mission_id' => $missionId, 'to_user_id' => $inviteUserId, 'from_user_id' => $fromUserId]);
    }
    
    /**
     * Get mission details
     *
     * @param int $inviteId
     * @return App\Models\MissionInvite
     */
    public function getDetails(int $inviteId): MissionInvite
    {
        return $this->missionInvite->getDetails($inviteId);
    }
}
