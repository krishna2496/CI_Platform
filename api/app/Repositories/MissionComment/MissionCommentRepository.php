<?php
namespace App\Repositories\MissionComment;

use Illuminate\Http\Request;
use App\Repositories\MissionComment\MissionCommentInterface;
use App\Models\Comment;
use App\Models\Mission;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Mission\MissionRepository;

class MissionCommentRepository implements MissionCommentInterface
{
    /**
     * @var App\Models\Comment
     */
    private $comment;

    /**
     * @var App\Models\Mission
     */
    private $mission;

    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    private $missionRepository;

    /**
     * Create a new mission comment repository instance.
     *
     * @param  App\Models\Comment $comment
     * @param  App\Models\Mission $mission
     * @param  App\Repositories\Mission\MissionRepository $missionRepository
     * @return void
     */
    public function __construct(Comment $comment, Mission $mission, MissionRepository $missionRepository)
    {
        $this->comment = $comment;
        $this->mission = $mission;
        $this->missionRepository = $missionRepository;
    }

    /**
     * Store mission comment
     *
     * @param int $userId
     * @param array $request
     * @return App\Models\Comment
     */
    public function store(int $userId, array $request): Comment
    {
        $request['user_id'] = $userId;
        return $this->comment->create($request);
    }

    /**
     * Get mission comments
     *
     * @param int $missionId
     * @param array $statusList
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getComments(int $missionId, array $statusList = [], Request $request = null): LengthAwarePaginator
    {
        $mission = $this->mission->findOrFail($missionId);

        $approvalStatusList = ($statusList) ? $statusList : [config("constants.comment_approval_status.PUBLISHED")];

        $commentQuery = $mission->comment()
        ->whereIn('approval_status', $approvalStatusList)
        ->with(['user:user_id,first_name,last_name,avatar']);

        $orderDirection = 'desc';
        if (isset($request)) {
            $orderDirection = ($request->has('order')) ? $request->input('order', 'desc') : 'desc';
        }
        $commentQuery = $commentQuery->orderBy('comment_id', $orderDirection);
        return $commentQuery->paginate(config("constants.MISSION_COMMENT_LIMIT"));
    }

    /**
     * Get comment detail
     *
     * @param int $commentId
     * @return App\Models\Comment
     */
    public function getComment(int $commentId): Comment
    {
        return $this->comment->findOrFail($commentId);
    }

    /**
     * Update comment, by commentId
     *
     * @param int $commentId
     * @param array $data
     * @return App\Models\Comment
     */
    public function updateComment(int $commentId, array $data): Comment
    {
        $comment = $this->comment->findOrFail($commentId);
        $comment->update($data);
        return $comment;
    }

    /**
     * Delete comment, by commentId
     *
     * @param int $commentId
     * @return bool
     */
    public function deleteComment(int $commentId): bool
    {
        $comment = $this->comment->findOrFail($commentId);
        return $comment->delete();
    }

    /**
     * Fetch user's comments on mission for dashboard
     *
     * @param int $userId
     * @param int $languageId
     * @param int $defaultTenantLanguageId
     * @param array|null $missionTypes
     * @return array
     */
    public function getUserComments(
        int $userId,
        int $languageId,
        int $defaultTenantLanguageId,
        array $missionTypes = null
    ): array {
        $comments = $this->comment
            ->where('user_id', $userId)
            ->orderby('created_at', 'desc')
            ->whereHas('mission', function ($query) use ($missionTypes) {
                $query->select('mission_id');
                if ($missionTypes !== null) {
                    $query->whereIn('mission_type', $missionTypes);
                }
            })
            ->get();

        $commentData = array();
        // Fetch comment counts by status
        if (count($comments) > 0) {
            // Count status
            $statusCount = $this->comment
                ->selectRaw("
                    COUNT(CASE WHEN approval_status = 'PUBLISHED' THEN 1 END) AS published,
                    COUNT(CASE WHEN approval_status = 'PENDING' THEN 1 END) AS pending,
                    COUNT(CASE WHEN approval_status = 'DECLINED' THEN 1 END) AS declined
                ")
                ->where('user_id', $userId)
                ->whereHas('mission', function ($query) use ($missionTypes) {
                    $query->select('mission_id');
                    if ($missionTypes !== null) {
                        $query->whereIn('mission_type', $missionTypes);
                    }
                })
                ->get();

            foreach ($comments as $value) {
                $value->title = $this->missionRepository
                    ->getMissionTitle(
                        $value->mission_id,
                        $languageId,
                        $defaultTenantLanguageId
                    );
                unset($value->mission);
            }
            $commentData['comments'] = $comments;
            $commentData['stats'] = $statusCount;
        }
        $comments = $commentData;
        return $comments;
    }

    /**
     * Delete comment by commentId
     *
     * @param int $commentId
     * @param int $userId
     * @return bool
     */
    public function deleteUsersComment(int $commentId, int $userId): bool
    {
        return $this->comment->where(['comment_id' => $commentId,
        'user_id' => $userId])->firstOrFail()->delete();
    }

    /**
     * Get comment details by comment id
     *
     * @param int $commentId
     * @return Comment
     */
    public function getCommentById(int $commentId): Comment
    {
        return $this->comment->with('user')->findOrFail($commentId);
    }

    /**
     * Get comment detail
     *
     * @param int $commentId
     * @return App\Models\Comment
     */
    public function getCommentDetail(int $commentId): Comment
    {
        return $this->comment->withTrashed()->find($commentId);
    }
}
