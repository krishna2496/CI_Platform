<?php
namespace App\Repositories\MissionMedia;

use App\Models\MissionMedia;
use Illuminate\Support\Collection;

interface MissionMediaInterface
{
    /**
     * Save media images
     *
     * @param array $mediaImages
     * @param string $tenantName
     * @param int $missionId
     * @return void
     */
    public function saveMediaImages(array $mediaImages, string $tenantName, int $missionId): void;

    /**
     * Save media vodeos
     *
     * @param array $mediaVideos
     * @param int $missionId
     * @return void
     */
    public function saveMediaVideos(array $mediaVideos, int $missionId): void;

    /**
     * Update media images
     *
     * @param array $mediaImages
     * @param string $tenantName
     * @param int $missionId
     * @return void
     */
    public function updateMediaImages(array $mediaImages, string $tenantName, int $missionId): void;

    /**
     * Update media videos
     *
     * @param array $mediaVideos
     * @param int $missionId
     * @return void
     */
    public function updateMediaVideos(array $mediaVideos, int $missionId): void;
    
    /**
     * Remove mission media
     *
     * @param int $mediaId
     * @return bool
     */
    public function deleteMedia(int $mediaId): bool;

    /**
     * Get mission media details
     *
     * @param int $mediaId
     * @return Collection
     */
    public function getMediaDetails(int $mediaId): Collection;
}
