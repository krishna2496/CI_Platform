<?php
namespace App\Repositories\MissionComment;

use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface MissionCommentInterface
{
    /**
     * Store a newly created resource in storage.
     *
     * @param array $request
     * @param int $userId
     * @return App\Models\Comment
     */
    public function store(int $userId, array $request): Comment;

    /**
     * Get mission comments
     *
     * @param int $missionId
     * @param array $statusList
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getComments(int $missionId, array $statusList = [], Request $request = null): LengthAwarePaginator;

    /**
     * Display user mission comments.
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
    ): array;

    /**
     * Delete comment by commentId
     *
     * @param int $commentId
     * @param int $userId
     * @return bool
     */
    public function deleteUsersComment(int $commentId, int $userId): bool;

    /**
     * Get comment detail
     *
     * @param int $commentId
     * @return App\Models\Comment
     */
    public function getCommentDetail(int $commentId): Comment;
}
