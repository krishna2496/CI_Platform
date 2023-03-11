<?php
namespace App\Repositories\MissionInvite;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\MissionInvite;

interface MissionInviteInterface
{
    /**
     * Check already invited for mission or not.
     *
     * @param int $missionId
     * @param int $inviteUserId
     * @param int $fromUserId
     * @return Illuminate\Support\Collection
     */
    public function getInviteMission(int $missionId, int $inviteUserId, int $fromUserId): Collection;

    /**
     * Invite for a mission.
     *
     * @param int $missionId
     * @param int $inviteUserId
     * @param int $fromUserId
     * @return MissionInvite
     */
    public function inviteMission(int $missionId, int $inviteUserId, int $fromUserId): MissionInvite;
}
