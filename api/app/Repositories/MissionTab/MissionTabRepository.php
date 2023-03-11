<?php
namespace App\Repositories\MissionTab;

use App\Repositories\MissionTab\MissionTabInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\S3Helper;
use App\Services\Mission\ModelsService;
use Ramsey\Uuid\Uuid;

class MissionTabRepository implements MissionTabInterface
{
    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\S3Helper
     */
    private $s3helper;

    /**
    * @var App\Services\Mission\ModelsService
    */
    private $modelsService;

    /**
    * Create a new Mission repository instance.
    *
    * @param  App\Helpers\LanguageHelper $languageHelper
    * @param  App\Helpers\Helpers $helpers
    * @param  App\Helpers\S3Helper $s3helper
    * @param  App\Services\Mission\ModelsService $modelsService
    * @return void
    */

    public function __construct(
        LanguageHelper $languageHelper,
        Helpers $helpers,
        S3Helper $s3helper,
        ModelsService $modelsService
    ) {
        $this->languageHelper = $languageHelper;
        $this->helpers = $helpers;
        $this->s3helper = $s3helper;
        $this->modelsService = $modelsService;
    }

    /**
    * Store a newly created resource into database
    *
    * @param \Illuminate\Http\Request $request
    * @param int $missionId
    * @return array
    */
    public function store(array $missionTabValue, int $missionId)
    {
        $languages = $this->languageHelper->getLanguages();

        $missionTabArray = [
            'mission_tab_id' => Uuid::uuid4()->toString(),
            'mission_id' => $missionId,
            'sort_key' => $missionTabValue['sort_key']
        ];
        $missionTab = $this->modelsService->missionTab->create($missionTabArray);
        foreach ($missionTabValue['translations'] as $missionTabTranslationsValue) {
            $language = $languages->where('code', $missionTabTranslationsValue['lang'])->first();
            $missionTabTranslationsArray = [
                'mission_tab_language_id' => Uuid::uuid4()->toString(),
                'mission_tab_id' => $missionTab['mission_tab_id'],
                'language_id' => $language->language_id,
                'name' => $missionTabTranslationsValue['name'],
                'section' => json_encode($missionTabTranslationsValue['sections'])
            ];
            $missionTabLanguage = $this->modelsService->missionTabLanguage->create($missionTabTranslationsArray);
            unset($missionTabTranslationsArray);
        }
        unset($missionTabArray);
    }

    /**
    * Store a newly created resource into database
    *
    * @param array $missionTabValue
    * @param int $missionId
    * @return array
    */
    public function update(array $missionTabValue, int $missionId)
    {
        $languages = $this->languageHelper->getLanguages();
        $missionTabId = $missionTabValue['mission_tab_id'];
        if (isset($missionTabValue['sort_key'])) {
            $missionTab = $this->modelsService->missionTab->where(["mission_tab_id"=>$missionTabId])->update(['sort_key'=>$missionTabValue['sort_key']]);
        }

        if (isset($missionTabValue['translations'])) {
            foreach ($missionTabValue['translations'] as $missionTabTranslationsValue) {
                $language = $languages->where('code', $missionTabTranslationsValue['lang'])->first();
                $missionTabTranslationsArray['mission_tab_language_id'] = Uuid::uuid4()->toString();
                $missionTabTranslationsArray['mission_tab_id'] = $missionTabId;
                $missionTabTranslationsArray['language_id'] = $language->language_id;
                                
                if (isset($missionTabTranslationsValue['name'])) {
                    $missionTabTranslationsArray['name'] = $missionTabTranslationsValue['name'];
                }
                if (isset($missionTabTranslationsValue['sections'])) {
                    $missionTabTranslationsArray['section'] = json_encode($missionTabTranslationsValue['sections']);
                }

                $missionTabLanguage = $this->modelsService->missionTabLanguage->createOrUpdateMissionTabLanguage(['mission_tab_id' => $missionTabId,
                                'language_id' => $language->language_id], $missionTabTranslationsArray);
                unset($missionTabTranslationsArray);
            }
        }
    }

    /**
     * Check sort key is already exist or not
     *
     * @param int $missionId
     * @param array $missionTabs
     * @return bool
     */
    public function checkSortKeyExist(int $missionId, array $missionTabs): bool
    {
        foreach ($missionTabs as $key => $value) {
            if (isset($value['sort_key'])) {
                if (isset($value['mission_tab_id'])) {
                    $result = $this->modelsService->missionTab->where([
                        ['mission_id', '=' ,$missionId],
                        ['sort_key', '=' ,$value['sort_key']],
                        ['mission_tab_id', '!=', $value['mission_tab_id']]
                    ])->get()->toArray();

                    if (count($result) >= 1) {
                        return false;
                    }
                } else {
                    $result = $this->modelsService->missionTab->where(
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
