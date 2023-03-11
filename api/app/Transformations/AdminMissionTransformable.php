<?php
namespace App\Transformations;

use App\Models\Mission;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait AdminMissionTransformable
{
    /**
     * Select admin mission fields
     *
     * @param App\Models\Mission | Illuminate\Pagination\LengthAwarePaginator $mission
     * @param Illuminate\Support\Collection $languages
     * @return App\Models\Mission | Illuminate\Pagination\LengthAwarePaginator
     */
    protected function adminTransformMission(
        $mission,
        Collection $languages
    ) {
        // Transform impact mission attribute
        if (isset($mission['impact'])) {
            $impactMission =  $mission['impact']->toArray();
            if ($impactMission != null) {
                $impactMissionDetails = [];
                foreach ($impactMission as $impactMissionKey => $impactMissionValue) {
                    $impactMissionDetails['mission_impact_id'] = $impactMissionValue['mission_impact_id'];
                    $impactMissionDetails['sort_key'] = $impactMissionValue['sort_key'];
                    $impactMissionDetails['icon_path'] = $impactMissionValue['icon_path'];
                    $impactMissionDetails['translations'] = [];
                    foreach ($impactMissionValue['mission_impact_language_details'] as $impactMissionLanguageValue) {
                        $languageCode = $languages
                            ->where('language_id', $impactMissionLanguageValue['language_id'])
                            ->first()
                            ->code;
                        $impactMissionLanguage['language_id'] = $impactMissionLanguageValue['language_id'];
                        $impactMissionLanguage['language_code'] = $languageCode;
                        $impactMissionLanguage['content'] = json_decode($impactMissionLanguageValue['content']);
                        array_push($impactMissionDetails['translations'], $impactMissionLanguage);
                    }
                    $mission['impact'][$impactMissionKey] = $impactMissionDetails;
                }
            }
        }

        return $mission;
    }
}
