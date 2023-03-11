<?php
namespace App\Repositories\MissionImpact;

use App\Models\MissionImpact;
use App\Helpers\LanguageHelper;
use App\Models\MissionImpactLanguage;
use App\Repositories\MissionImpact\MissionImpactInterface;
use App\Helpers\S3Helper;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MissionImpactRepository implements MissionImpactInterface
{
    /**
     * @var App\Models\MissionImpact
     */
    private $missionImpactModel;

    /**
     * @var App\Models\MissionImpactLanguage
     */
    private $missionImpactLanguageModel;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Helpers\S3Helper
     */
    private $s3Helper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * Create a new MissionImpact repository instance.
     *
     * @param App\Models\MissionImpact $missionImpactModel
     * @param App\Models\MissionImpactLanguage $missionImpactLanguageModel
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param App\Helpers\S3Helper $s3Helper
     */
    public function __construct(
        MissionImpact $missionImpactModel,
        MissionImpactLanguage $missionImpactLanguageModel,
        LanguageHelper $languageHelper,
        S3Helper $s3Helper,
        Helpers $helpers
    ) {
        $this->missionImpactModel = $missionImpactModel;
        $this->missionImpactLanguageModel = $missionImpactLanguageModel;
        $this->languageHelper = $languageHelper;
        $this->s3Helper = $s3Helper;
        $this->helpers = $helpers;
    }

    /**
     * Save impact mission details
     *
     * @param array $missionImpact
     * @param int $missionId
     * @param int $defaultTenantLanguageId
     * @param string $tenantName
     * @return void
     */
    public function store(array $missionImpact, int $missionId, int $defaultTenantLanguageId, string $tenantName)
    {
        $languages = $this->languageHelper->getLanguages();
        $missionImpactPostData = [
            'mission_id' => $missionId,
            'sort_key' => $missionImpact['sort_key']
        ];

        $missionImpactModelData = $this->missionImpactModel->create($missionImpactPostData);
        $missionImpactId = $missionImpactModelData->mission_impact_id;

        if (isset($missionImpact['icon_path']) && !empty($missionImpact['icon_path'])) {
            $iconPath = $this->s3Helper->uploadFileOnS3Bucket(
                $missionImpact['icon_path'],
                $tenantName,
                "missions/$missionId/impact/$missionImpactId"
            );
            $missionImpactModelData->update(['icon_path' => $iconPath]);
        }

        foreach ($missionImpact['translations'] as $missionImpactValue) {
            $language = $languages
                ->where('code', $missionImpactValue['language_code'])
                ->first();
            $missionImpactLanguagePostData = [
                'mission_impact_id' => $missionImpactModelData['mission_impact_id'],
                'language_id' => !empty($language) ? $language->language_id : $defaultTenantLanguageId,
                'content' => json_encode($missionImpactValue['content'])
            ];
            $this->missionImpactLanguageModel->create($missionImpactLanguagePostData);
            unset($missionImpactLanguagePostData);
        }

        unset($missionImpactPostData);
    }

    /**
    * Update mission impact details
    *
    * @param array $missionImpact
    * @param int $missionId
    * @param int $defaultTenantLanguageId
    * @param string $tenantName
    * @return void
    */
    public function update(array $missionImpact, int $missionId, int $defaultTenantLanguageId, string $tenantName)
    {
        $languages = $this->languageHelper->getLanguages();
        $missionImpactId = $missionImpact['mission_impact_id'];

        // Update sort_key
        if (isset($missionImpact['sort_key']) && !empty($missionImpact['sort_key'])) {
            $this->missionImpactModel
                ->where(['mission_impact_id' => $missionImpactId])
                ->update(['sort_key' => $missionImpact['sort_key']]);
        }

        // Update icon
        if (isset($missionImpact['icon_path']) && !empty($missionImpact['icon_path'])) {
            $iconPath = $this->s3Helper->uploadFileOnS3Bucket(
                $missionImpact['icon_path'],
                $tenantName,
                "missions/$missionId/impact/$missionImpactId"
            );

            $this->missionImpactModel
                ->where(['mission_impact_id' => $missionImpactId])
                ->update(['icon_path' => $iconPath]);
        }

        if (isset($missionImpact['translations'])) {
            foreach ($missionImpact['translations'] as $missionImpactLanguageValue) {
                $language = $languages
                    ->where('code', $missionImpactLanguageValue['language_code'])
                    ->first();
                $missionImpactPostData['mission_impact_id'] = $missionImpactId;
                $missionImpactPostData['language_id'] = !empty($language) ? $language->language_id : $defaultTenantLanguageId;

                if (isset($missionImpactLanguageValue['content'])) {
                    $missionImpactPostData['content'] = json_encode($missionImpactLanguageValue['content']);
                }

                $languageId = !empty($language) ? $language->language_id : $defaultTenantLanguageId;
                $this->missionImpactLanguageModel
                    ->createOrUpdateMissionImpactTranslation(
                        [
                            'mission_impact_id' => $missionImpactId,
                            'language_id' => $languageId
                        ],
                        $missionImpactPostData
                    );
                unset($missionImpactPostData);
            }
        }
    }

    /**
     * Delete mission impact and s3bucket data
     *
     * @param string $missionImpactId
     * @return bool
     */
    public function deleteMissionImpactAndS3bucketData(string $missionImpactId): bool
    {
        $missionImpactData = $this->missionImpactModel->select('icon_path')
            ->where(['mission_impact_id' => $missionImpactId, ['deleted_at', '=', null]])->get()->toArray();
        
        if (!empty($missionImpactData)) {
            $iconPath = $missionImpactData[0]['icon_path'];
            $storageIconPath = parse_url($iconPath);
            $trimIconPath = ltrim($storageIconPath['path'], '/');
            if (Storage::disk('s3')->exists($trimIconPath)) {
                Storage::disk('s3')->delete($trimIconPath);
            }
        }

        return $this->missionImpactModel->deleteMissionImpact($missionImpactId);
    }

    /**
     * Check sort key is already exist or not
     *
     * @param int $missionId
     * @param array $missionImpact
     * @return bool
     */
    public function checkImpactSortKeyExist(int $missionId, array $missionImpact): bool
    {
        foreach ($missionImpact as $key => $value) {
            if (isset($value['sort_key'])) {
                if (isset($value['mission_impact_id'])) {
                    $result = $this->missionImpactModel->where([
                        ['mission_id', '=' ,$missionId],
                        ['sort_key', '=' ,$value['sort_key']],
                        ['mission_impact_id', '!=', $value['mission_impact_id']]
                    ])->get()->toArray();

                    if (count($result) >= 1) {
                        return false;
                    }
                } else {
                    $result = $this->missionImpactModel->where(
                        [ 'mission_id' => $missionId, 'sort_key' => $value['sort_key']]
                    )->get()->toArray();
                    
                    if (count($result) >= 1) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
