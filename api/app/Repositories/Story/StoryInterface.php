<?php
namespace App\Repositories\Story;

use App\Models\Story;
use App\Models\StoryMedia;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StoryInterface
{
    /**
     * Store story details
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\Story
     */
    public function store(Request $request): Story;

    /**
     * Update story details
     *
     * @param \Illuminate\Http\Request $request
     * @param int $storyId
     * @return App\Models\Story
     */
    public function update(Request $request, int $storyId): Story;

    /**
     * Remove the story details.
     *
     * @param  int  $storyId
     * @param  int  $userId
     * @return bool
     */
    public function delete(int $storyId, int $userId): bool;

    /**
     * Display a user story listing with pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $languageId
     * @param int $userId
     * @param string $status
     * @param null|array $missionTypes
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserStoriesWithPagination(
        Request $request,
        int $languageId,
        int $userId = null,
        string $status = null,
        array $missionTypes = null
    ): LengthAwarePaginator;


    /**
     * Get story details.
     *
     * @param int $storyId
     * @param string $storyStatus
     * @param int $userId
     * @param array $allowedStoryStatus
     * @param null|array $missionTypes
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getStoryDetails(
        int $storyId,
        string $storyStatus = null,
        int $userId = 0,
        array $allowedStoryStatus = [],
        array $missionTypes = []
    ): Collection;
    /**
     * Update story status field value, based on story_id condition
     *
     * @param int $storyStatus
     * @param int $Id
     * @return bool
     */
    public function updateStoryStatus(string $storyStatus, int $storyId): bool;

    /**
     * Do copy of declined story data
     *
     * @param int $oldStoryId
     * @return int $newStoryId
     */
    public function createStoryCopy(int $oldStoryId): int;

    /**
     * Display a listing of specified resources without pagination.
     *
     * @param int $languageId
     * @param int $userId
     * @param null|array $missionTypes
     * @return Object
     */
    public function getUserStories(int $languageId, int $userId, array $missionTypes = null): Object;


    /**
     * Store story images.
     *
     * @param string $tenantName
     * @param int $storyId
     * @param array $storyImages
     * @param int $userId
     * @return void
     */
    public function storeStoryImages(
        string $tenantName,
        int $storyId,
        array $storyImages,
        int $userId
    ): void;

    /**
     * Store story videos url.
     *
     * @param string $storyVideosUrl
     * @param int $storyId
     * @return void
     */
    public function storeStoryVideoUrl(string $storyVideosUrl, int $storyId): void;

    /**
     * Check story status
     *
     * @param int $userId
     * @param int $storyId
     * @param array $storyStatus
     *
     * @return bool
     */
    public function checkStoryStatus(int $userId, int $storyId, array $storyStatus): bool;


    /**
     * Used for check if story exist or not
     *
     * @param int $storyId
     * @return Story
     */
    public function checkStoryExist(int $storyId): Story;


    /**
     * Submit story details
     *
     * @param int $userId
     * @param int $storyId
     * @return App\Models\Story
     */
    public function submitStory(int $userId, int $storyId): Story;

    /**
     * Find story by user id
     *
     * @param int $userId
     * @param int $storyId
     * @return App\Models\Story
     */
    public function findStoryByUserId(int $userId, int $storyId): Story;

    /**
     * Get user stories status count
     *
     * @param int $userId
     * @param null|array $missionTypes
     * @return App\Models\Story
     */
    public function getUserStoriesStatusCounts(int $userId, array $missionTypes = null): Story;

    /**
     * Get story media from storyid
     *
     * @param int $stroyId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getStoryMedia(int $storyId): Collection;

    /**
     * Get story detail.
     *
     * @param int $storyId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getStoryDetail(int $storyId): Collection;
}
