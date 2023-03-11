<?php
namespace App\Repositories\MissionMedia;

use App\Repositories\MissionMedia\MissionMediaInterface;
use App\Models\MissionMedia;
use Illuminate\Support\Collection;
use App\Helpers\S3Helper;

class MissionMediaRepository implements MissionMediaInterface
{
    /**
     * @var App\Models\MissionMedia
     */
    public $missionMedia;

    /**
     * @var App\Helpers\S3Helper
     */
    private $s3helper;

    /**
     * Create a new MissionMedia repository instance.
     *
     * @param  App\Models\MissionMedia $missionMedia
     * @param  App\Helpers\S3Helper $s3helper
     * @return void
     */
    public function __construct(
        MissionMedia $missionMedia,
        S3Helper $s3helper
    ) {
        $this->missionMedia = $missionMedia;
        $this->s3helper = $s3helper;
    }

    /**
     * Save media images
     *
     * @param array $mediaImages
     * @param string $tenantName
     * @param int $missionId
     * @return void
     */
    public function saveMediaImages(array $mediaImages, string $tenantName, int $missionId): void
    {
        $isDefault = 0;
        foreach ($mediaImages as $value) {
            // Check for default image in mission_media
            $default = (isset($value['default']) && ($value['default'] !== '')) ? $value['default'] : '0';
            if ($default === '1') {
                $isDefault = 1;
                $media = array('default' => '0');
                $this->missionMedia->where('mission_id', $missionId)->update($media);
            }

            $missionMedia = array(
                'mission_id' => $missionId,
                'default' => $default,
                'sort_order' => $value['sort_order']
            );

            if (isset($value['internal_note'])) {
                $missionMedia['internal_note'] = $value['internal_note'];
            }

            $mediaData = $this->missionMedia->create($missionMedia);
            $mediaId = $mediaData->mission_media_id;

            $filePath = $this->s3helper->uploadFileOnS3Bucket(
                $value['media_path'],
                $tenantName,
                "missions/$missionId/media/$mediaId"
            );
            $mediaData->update([
                'media_name' => basename($filePath),
                'media_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                'media_path' => $filePath,
            ]);

            unset($missionMedia);
        }

        if ($isDefault ===  0) {
            $mediaData = $this->missionMedia
                ->where([
                    ['mission_id', '=', $missionId],
                    ['media_type', '<>', 'mp4']
                ])
                ->orderBy('sort_order', 'ASC')
                ->first();
            $missionMedia = array('default' => '1');
            $this->missionMedia
                ->where('mission_media_id', $mediaData->mission_media_id)
                ->update($missionMedia);
        }
    }

    /**
     * Save media vodeos
     *
     * @param array $mediaVideos
     * @param int $missionId
     * @return void
     */
    public function saveMediaVideos(array $mediaVideos, int $missionId): void
    {
        foreach ($mediaVideos as $value) {
            $missionMedia = array('mission_id' => $missionId,
                                  'media_name' => $value['media_name'],
                                  'media_type' => 'mp4',
                                  'media_path' => $value['media_path'],
                                  'sort_order' => $value['sort_order']);
            if (isset($value['internal_note'])) {
                $missionMedia['internal_note'] = $value['internal_note'];
            }
            $this->missionMedia->create($missionMedia);
            unset($missionMedia);
        }
    }

    /**
     * Update media images
     *
     * @param array $mediaImages
     * @param string $tenantName
     * @param int $missionId
     * @return void
     */
    public function updateMediaImages(array $mediaImages, string $tenantName, int $missionId): void
    {
        $isDefault = 0;
        foreach ($mediaImages as $value) {
            $missionMedia = array();
            if (isset($value['default'])) {
                // Check for default image in mission_media
                $default = (isset($value['default']) && ($value['default'] !== '')) ? $value['default'] : '0';
                if ($default === '1') {
                    $isDefault = 1;
                    $this->missionMedia->where('mission_id', $missionId)->update(['default' => '0']);
                }
                $missionMedia['default'] = $default;
            }
            if (isset($value['sort_order'])) {
                $missionMedia['sort_order'] = $value['sort_order'];
            }
            if (isset($value['internal_note'])) {
                $missionMedia['internal_note'] = $value['internal_note'];
            }

            $mediaData = $this->missionMedia->createOrUpdateMedia([
                'mission_id' => $missionId,
                'mission_media_id' => $value['media_id']
            ], $missionMedia);

            if (isset($value['media_path'])) {
                $mediaId = $mediaData->mission_media_id;
                $filePath = $this->s3helper->uploadFileOnS3Bucket(
                    $value['media_path'],
                    $tenantName,
                    "missions/$missionId/media/$mediaId"
                );
                $mediaData->update([
                    'media_name' => basename($filePath),
                    'media_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                    'media_path' => $filePath
                ]);
            }

            unset($missionMedia);
        }
        $defaultData = $this->missionMedia->where('mission_id', $missionId)->where('default', '1')->count();

        if (($isDefault === 0) && ($defaultData === 0)) {
            $mediaData = $this->missionMedia
                ->where([
                    ['mission_id', '=', $missionId],
                    ['media_type', '<>', 'mp4']
                ])
                ->orderBy('sort_order', 'ASC')
                ->first();
            $this->missionMedia
                ->where('mission_media_id', $mediaData->mission_media_id)
                ->update(['default' => '1']);
        }
    }

    /**
     * Update media videos
     *
     * @param array $mediaVideos
     * @param int $id
     * @return void
     */
    public function updateMediaVideos(array $mediaVideos, int $id): void
    {
        foreach ($mediaVideos as $value) {
            $missionMedia = array();
            if (isset($value['media_path'])) {
                $missionMedia = array('media_type' => 'mp4', 'media_path' => $value['media_path']);
            }

            if (isset($value['media_name'])) {
                $missionMedia['media_name'] = $value['media_name'];
            }
            if (isset($value['sort_order'])) {
                $missionMedia['sort_order'] = $value['sort_order'];
            }
            if (isset($value['internal_note'])) {
                $missionMedia['internal_note'] = $value['internal_note'];
            }
            $this->missionMedia->createOrUpdateMedia(['mission_id' => $id,
             'mission_media_id' => $value['media_id']], $missionMedia);
            unset($missionMedia);
        }
    }

    /**
     * Remove mission media
     *
     * @param int $mediaId
     * @return bool
     */
    public function deleteMedia(int $mediaId): bool
    {
        $mediaDetails = $this->getMediaDetails($mediaId);
        if ($mediaDetails->count() > 0 && $mediaDetails[0]['default'] == '1') {
            $firstImageMedia = $this->missionMedia
                ->where([
                    ['mission_id', '=', $mediaDetails[0]['mission_id']],
                    ['media_type', '<>', 'mp4'],
                    ['mission_media_id', '<>', $mediaId]
                ])
                ->orderBy('sort_order', 'ASC')
                ->first();
            if ($firstImageMedia) {
                $this->missionMedia
                    ->where('mission_media_id', $firstImageMedia->mission_media_id)
                    ->update(['default' => '1']);
            }
        }
        return $this->missionMedia->deleteMedia($mediaId);
    }

    /**
     * Get mission media details
     *
     * @param int $mediaId
     * @return Collection
     */
    public function getMediaDetails(int $mediaId): Collection
    {
        return $this->missionMedia->where('mission_media_id', $mediaId)->get();
    }

    /**
     * Get mission media details
     *
     * @param int $mediaId
     * @return App\Models\MissionMedia
     */
    public function find(int $mediaId): MissionMedia
    {
        return $this->missionMedia->findOrFail($mediaId);
    }

    /**
     * Check media is linked with mission or not
     *
     * @param int $mediaId
     * @return bool
     */
    public function isMediaLinkedToMission(int $mediaId, int $missionId): bool
    {
        $media = $this->missionMedia->where(['mission_media_id' => $mediaId, 'mission_id' => $missionId])->first();
        return ($media === null) ? false : true;
    }
}
