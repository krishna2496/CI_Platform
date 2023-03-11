<?php

namespace App\Repositories\Story;

use App\Events\Story\StoryDeletedEvent;
use App\Helpers\Helpers;
use App\Helpers\S3Helper;
use App\Models\Story;
use App\Models\StoryMedia;
use App\Repositories\Story\StoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StoryRepository implements StoryInterface
{
    /**
     *
     * @var App\Models\Story
     */
    private $story;

    /**
     *
     * @var App\Models\StoryMedia
     */
    private $storyMedia;

    /**
     *
     * @var App\Helpers\S3Helper
     */
    private $s3helper;

    /**
     *
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * Create a new Story repository instance.
     *
     * @param  App\Models\Story $story
     * @param  App\Models\StoryMedia $storyMedia
     * @param  App\Helpers\S3Helper $s3helper
     * @param  App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(
        Story $story,
        StoryMedia $storyMedia,
        S3Helper $s3helper,
        Helpers $helpers
    ) {
        $this->story = $story;
        $this->storyMedia = $storyMedia;
        $this->s3helper = $s3helper;
        $this->helpers = $helpers;
    }

    /**
     * Store story details
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\Story
     */
    public function store(Request $request): Story
    {
        $storyDataArray = array(
            'mission_id' => $request->mission_id,
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => $request->auth->user_id,
            'status' => config('constants.story_status.DRAFT'),
        );

        $storyData = $this->story->create($storyDataArray);

        if ($request->hasFile('story_images')) {
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            // Store story images
            $this->storeStoryImages(
                $tenantName,
                $storyData->story_id,
                $request->file('story_images'),
                $request->auth->user_id
            );
        }

        if ($request->has('story_videos')) {
            // Store story video url
            $this->storeStoryVideoUrl($request->story_videos, $storyData->story_id);
        }
        return $storyData;
    }

    /**
     * Update story details
     *
     * @param \Illuminate\Http\Request $request
     * @param int $storyId
     * @return App\Models\Story
     */
    public function update(Request $request, int $storyId): Story
    {
        // Find story
        $story = $this->findStoryByUserId($request->auth->user_id, $storyId);


        $storyDataArray = $request->except(['user_id', 'published_at', 'status']);
        $storyDataArray['status'] = config('constants.story_status.DRAFT');
        $story->update($storyDataArray);

        if ($request->hasFile('story_images')) {
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            // Store story images
            $this->storeStoryImages(
                $tenantName,
                $story->story_id,
                $request->file('story_images'),
                $request->auth->user_id
            );
        }

        if ($request->has('story_videos')) {
            // Store story video url
            $this->storeStoryVideoUrl($request->story_videos, $story->story_id);
        }

        return $story;
    }

    /**
     * Remove the story details.
     *
     * @param  int  $storyId
     * @param  int  $userId
     * @return bool
     */
    public function delete(int $storyId, int $userId): bool
    {
        $wasDeleted = $this->story->deleteStory($storyId, $userId);
        event(new StoryDeletedEvent($storyId));

        return $wasDeleted;

    }

    /**
     * Display a listing of specified resources with pagination.
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
    ): LengthAwarePaginator {
        $userStoryQuery = $this->story->select(
            'story_id',
            'user_id',
            'mission_id',
            'title',
            'description',
            'status',
            'published_at',
            'created_at'
        )->with([
            'user',
            'mission',
            'mission.missionTheme',
            'storyMedia',
            'mission.missionLanguage' => function ($query) use ($languageId) {
                $query->select(
                    'mission_language_id',
                    'mission_id',
                    'title'
                )->where('language_id', $languageId);
            },
        ])
        ->whereHas('mission', function($query) use ($missionTypes) {
            if ($missionTypes !== null) {
                $query->whereIn('mission_type', $missionTypes);
            }
        })
        ->whereHas('user');

        if ($request->has('search') && $request->has('search') !== '') {
            $userStoryQuery->where(function ($query) use ($request) {
                $query->orWhere('title', 'like', '%' . $request->input('search') . '%');
                $query->orWhere('description', 'like', '%' . $request->input('search') . '%');
            });
        }

        if ($request->has('status') && $request->input('status') !== "") {
            $userStoryQuery->where(function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            });
        }

        if ($request->has('mission_id') && $request->input('mission_id') !== "") {
            $userStoryQuery->where(function ($query) use ($request) {
                $query->where('mission_id', $request->input('mission_id'));
            });
        }

        $userStoryQuery->when($userId, function ($query, $userId) {
            return $query->where('user_id', $userId);
        });
        $userStoryQuery->when($status, function ($query, $status) {
            return $query->where('status', $status);
        });
        return $userStoryQuery->paginate($request->perPage);
    }

    /**
     * Update story status field value, based on storyId condition
     *
     * @param string $storyStatus
     * @param int $storyId
     * @return bool
     */
    public function updateStoryStatus(string $storyStatus, int $storyId): bool
    {
        // default story array to update
        $updateData = [
            'status' => $storyStatus,
            'published_at' => null,
        ];

        if ($storyStatus == 'PUBLISHED') {
            $updateData['published_at'] = Carbon::now()->toDateTimeString();
        }
        return $this->story->where('story_id', $storyId)
            ->update($updateData);
    }

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
        array $missionTypes = null
    ): Collection {
        $storyQuery = $this->story->with([
            'user',
            'user.city.languages',
            'user.country.languages',
            'storyMedia',
            'mission',
            'mission.missionLanguage',
        ]);

        if ($missionTypes !== null) {
            $storyQuery->whereHas('mission', function($query) use ($missionTypes) {
                $query->whereIn('mission_type', $missionTypes);
            });
        }

        $storyQuery->Where(function($query) use ($storyId, $storyStatus) {
            $query->when($storyId, function ($subQuery) use ($storyId) {
                return $subQuery->where('story_id', $storyId);
            })->when($storyStatus, function ($subQuery) use ($storyStatus) {
                return $subQuery->where('status', $storyStatus);
            });
        });

        // Only story creater can access DRAFT story
        if (!empty($userId) && !empty($allowedStoryStatus)) {
            $storyQuery->orWhere(function ($query) use ($userId, $allowedStoryStatus, $storyId) {
                $query->where('user_id', $userId)
                ->where('story_id', $storyId)
                ->whereIn('status', $allowedStoryStatus);
            });
        }

        return $storyQuery->get();
    }

    /**
     * Create story copy from old story
     *
     * @param int $oldStoryId
     * @return int $newStoryId
     */
    public function createStoryCopy(int $oldStoryId): int
    {
        $newStory = $this->story->with(['storyMedia'])->findOrFail($oldStoryId)->replicate();
        $newStory->title = trans('general.labels.TEXT_STORY_COPY_OF') . $newStory->title;
        $newStory->status = config('constants.story_status.DRAFT');
        $newStory->save();


        $newStoryId = $newStory->story_id;
        $storyMedia =[];
        foreach ($newStory->storyMedia as $media) {
            $storyMedia[] = new StoryMedia([
                'type' => $media->type,
                'path' => $media->path
            ]);
        }
        $newStory->storyMedia()->saveMany($storyMedia);
        return $newStoryId;
    }

    /**
     * Display a listing of specified resources without pagination.
     *
     * @param int $languageId
     * @param int $userId
     * @param null|array $missionTypes
     * @return Object
     */
    public function getUserStories(
        int $languageId,
        int $userId,
        array $missionTypes = null,
        $storyId = null
    ): Object {
        $userStoryQuery = $this->story->select(
            'story_id',
            'mission_id',
            'title',
            'description',
            'status',
            'published_at',
            'created_at'
        )
        ->whereHas('mission', function($query) use ($missionTypes) {
            if ($missionTypes !== null) {
                $query->whereIn('mission_type', $missionTypes);
            }
        })
        ->with(['mission', 'mission.missionLanguage' => function ($query) use ($languageId) {
            $query->select('mission_language_id', 'mission_id', 'title')
                    ->where('language_id', $languageId);
        }])->where('user_id', $userId);

        if ($storyId !== null) {
            $userStoryQuery->where('story_id', $storyId);
        }

        return $userStoryQuery->get();
    }

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
    ): void {
        foreach ($storyImages as $file) {
            $filePath = $this->s3helper
                ->uploadDocumentOnS3Bucket(
                    $file,
                    $tenantName,
                    $userId,
                    config('constants.folder_name.story')
                );
            $storyImage = array('story_id' => $storyId,
                'type' => 'image',
                'path' => $filePath);
            $this->storyMedia->create($storyImage);
        }
    }

    /**
     * Store story videos url.
     *
     * @param string $storyVideosUrl
     * @param int $storyId
     * @return void
     */
    public function storeStoryVideoUrl(string $storyVideosUrl, int $storyId): void
    {
        $storyVideo = array('story_id' => $storyId,
            'type' => 'video',
            'path' => $storyVideosUrl);
        if (strlen(trim($storyVideosUrl)) == 0) {
            $this->storyMedia->where(['story_id' => $storyId,
            'type' => 'video'])->delete();
        } else {
            $this->storyMedia->updateOrCreate(['story_id' => $storyId, 'type' => 'video'], ['path' => $storyVideosUrl]);
        }
    }

    /**
     * Check story status
     *
     * @param int $userId
     * @param int $storyId
     * @param array $storyStatus
     *
     * @return bool
     */
    public function checkStoryStatus(int $userId, int $storyId, array $storyStatus): bool
    {
        $storyDetails = $this->story
            ->where(['user_id' => $userId, 'story_id' => $storyId])
            ->whereIn('status', $storyStatus)
            ->get();
        $storyStatus = ($storyDetails->count() > 0) ? false : true;
        return $storyStatus;
    }

    /**
     * Submit story for admin approval
     *
     * @param int $userId
     * @param int $storyId
     * @return App\Models\Story
     */
    public function submitStory(int $userId, int $storyId): Story
    {
        // Find story
        $story = $this->findStoryByUserId($userId, $storyId);
        if ($story->status == config('constants.story_status.DRAFT')) {
            $story->update(['status' => config('constants.story_status.PENDING')]);
        }
        return $story;
    }

    /**
     * Find story by user id
     *
     * @param int $userId
     * @param int $storyId
     * @return App\Models\Story
     */
    public function findStoryByUserId(int $userId, int $storyId): Story
    {
        $story = $this->story->where(['story_id' => $storyId,
            'user_id' => $userId])->firstOrFail();

        return $story;
    }

    /**
     * Remove story image.
     *
     * @param int $mediaId
     * @param int $storyId
     * @return bool
     */
    public function deleteStoryImage(int $mediaId, int $storyId): bool
    {
        return $this->storyMedia->deleteStoryImage($mediaId, $storyId);
    }

    /**
     * Used for check if story exist or not
     *
     * @param int $storyId
     * @return Story
     */
    public function checkStoryExist(int $storyId): Story
    {
        return $this->story->findOrFail($storyId);
    }

    /**
     * Get user stories status count
     *
     * @param int $userId
     * @param null|array $missionTypes
     * @return App\Models\Story
     */
    public function getUserStoriesStatusCounts(int $userId, array $missionTypes = null): Story
    {
        return $this->story
            ->whereHas('mission', function($query) use ($missionTypes) {
                if ($missionTypes !== null) {
                    $query->whereIn('mission_type', $missionTypes);
                }
            })
            ->selectRaw("
                COUNT(CASE WHEN status = 'DRAFT' THEN 1 END) AS draft,
                COUNT(CASE WHEN status = 'PENDING' THEN 1 END) AS pending,
                COUNT(CASE WHEN status = 'PUBLISHED' THEN 1 END) AS published,
                COUNT(CASE WHEN status = 'DECLINED' THEN 1 END) AS declined")
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get story media from storyid
     *
     * @param int $stroyId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getStoryMedia(int $storyId): Collection
    {
        return $this->storyMedia->where('story_id', $storyId)->get();
    }

    /**
     * Get story detail.
     *
     * @param int $storyId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getStoryDetail(int $storyId): Collection
    {
        return $this->story->withTrashed()->where('story_id', $storyId)->get();
    }
}
