<?php

namespace App\Transformations;

use App\Models\Mission;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait MissionTransformable
{
    /**
     * Select mission fields.
     *
     * @param App\Models\Mission $mission
     * @param string $languageCode
     * @param int $languageId
     * @param int $defaultTenantLanguage
     * @param string $timezone
     * @param object $tenantLanguages
     * @return App\Models\Mission
     */
    protected function transformMission(
        Mission $mission,
        string $languageCode,
        int $languageId,
        int $defaultTenantLanguage,
        string $timezone,
        Collection $tenantLanguages
    ): Mission {
        if (isset($mission['goalMission']) && is_numeric($mission['goalMission']['goal_objective'])) {
            $mission['goal_objective'] = $mission['goalMission']['goal_objective'];
        }

        if (isset($mission['start_date'])) {
            $mission['start_date'] = Carbon::parse(
                $mission['start_date'],
                config('constants.TIMEZONE')
            )->setTimezone($timezone)->toDateTimeString();
        }

        if (isset($mission['end_date'])) {
            $mission['end_date'] = Carbon::parse(
                $mission['end_date'],
                config('constants.TIMEZONE')
            )->setTimezone($timezone)->toDateTimeString();
        }
        if (isset($mission['timeMission'])) {
            $mission['application_deadline'] = isset($mission['timeMission']['application_deadline']) ? Carbon::parse(
                $mission['timeMission']['application_deadline'],
                config('constants.TIMEZONE')
            )->setTimezone($timezone)->toDateString() : null;

            $mission['application_start_date'] =
                isset($mission['timeMission']['application_start_date']) ? Carbon::parse(
                    $mission['timeMission']['application_start_date'],
                    config('constants.TIMEZONE')
                )->setTimezone($timezone)->toDateString() : null;

            $mission['application_end_date'] = isset($mission['timeMission']['application_end_date']) ? Carbon::parse(
                $mission['timeMission']['application_end_date'],
                config('constants.TIMEZONE')
            )->setTimezone($timezone)->toDateString() : null;

            $mission['application_start_time'] = isset($mission['timeMission']['application_start_time']) ?
                Carbon::parse(
                    $mission['timeMission']['application_start_time'],
                    config('constants.TIMEZONE')
                )->setTimezone($timezone)->toDateTimeString() : null;

            $mission['application_end_time'] = isset($mission['timeMission']['application_end_time']) ?
                Carbon::parse(
                    $mission['timeMission']['application_end_time'],
                    config('constants.TIMEZONE')
                )->setTimezone($timezone)->toDateTimeString() : null;
        }
        unset($mission['goalMission']);
        unset($mission['timeMission']);
        $mission['achieved_goal'] = $mission['achieved_goal'] ?? '';
        $mission['user_application_status'] = ($mission['missionApplication'][0]['approval_status']) ?? '';
        $mission['rating'] = ($mission['missionRating'][0]['rating']) ?? 0;
        $mission['is_favourite'] = ($mission['favourite_mission_count'] && ($mission['favourite_mission_count'] !== 0))
        ? 1 : 0;
        unset($mission['missionRating']);
        unset($mission['favouriteMission']);
        unset($mission['missionApplication']);

        if (isset($mission['volunteeringAttribute']['availability'])) {
            $arrayKey = array_search($languageCode, array_column($mission['volunteeringAttribute']['availability']['translations'], 'lang'));
            if ($arrayKey  !== '') {
                $mission['availability_type'] = $mission['volunteeringAttribute']['availability']['translations'][$arrayKey]['title'];
            }
            unset($mission['volunteeringAttribute']['availability']);
        }

        if (isset($mission['volunteeringAttribute'])) {
            $mission['availability_id'] = $mission['volunteeringAttribute']['availability_id'];
            $mission['is_virtual'] = $mission['volunteeringAttribute']['is_virtual'];
            $mission['total_seats'] = $mission['volunteeringAttribute']['total_seats'];
            unset($mission['volunteeringAttribute']);
        }

        // Set seats_left or already_volunteered
        if ($mission['total_seats'] !== 0 && $mission['total_seats'] !== null) {
            $mission['seats_left'] = ($mission['total_seats']) -
            ($mission['mission_application_count']);
        } else {
            $mission['already_volunteered'] = $mission['mission_application_count'];
        }

        // Get defalut media image
        $mission['default_media_type'] = $mission['missionMedia'][0]['media_type'] ?? '';
        $mission['default_media_path'] = $mission['missionMedia'][0]['media_path'] ?? '';
        unset($mission['missionMedia']);
        unset($mission['city']);

        $key = array_search($languageId, array_column($mission['missionLanguage']->toArray(), 'language_id'));
        $language = ($key === false) ? $defaultTenantLanguage : $languageId;
        $missionLanguage = $mission['missionLanguage']->where('language_id', $language)->first();

        // Set title and description
        $mission['title'] = $missionLanguage->title ?? '';
        $mission['short_description'] = $missionLanguage->short_description ?? '';
        if (isset($missionLanguage->description)) {
            $mission['description'] = $missionLanguage->description ?? '';
        }
        $mission['objective'] = $missionLanguage->objective ?? '';

        $mission['label_goal_achieved'] =  $missionLanguage->label_goal_achieved ?? '';
        $mission['label_goal_objective'] =  $missionLanguage->label_goal_objective ?? '';
        $mission['custom_information'] = $missionLanguage->custom_information ?? null;
        unset($mission['missionLanguage']);
        // Check for apply in mission validity
        $mission['set_view_detail'] = 0;

        $todayDate = Carbon::parse(date(config('constants.DB_DATE_FORMAT')));
        $today = $todayDate->setTimezone(config('constants.TIMEZONE'))->format(config('constants.DB_DATE_FORMAT'));
        $todayTime = $this->helpers->getUserTimeZoneDate(date(config("constants.DB_DATE_TIME_FORMAT")));

        if ($mission['volunteeringAttribute']) {
            if (($mission['user_application_count'] > 0) ||
            ($mission['volunteeringAttribute']['total_seats'] !== 0 && $mission['volunteeringAttribute']['total_seats'] === $mission['mission_application_count']) ||
            ($mission['end_date'] !== null && $mission['end_date'] <= $today)
            ) {
                $mission['set_view_detail'] = 1;
            }
        }

        if (isset($mission['application_deadline']) && ($mission['application_deadline'] !== null) &&
         ($mission['application_deadline'] < $today)) {
            $mission['set_view_detail'] = 1;
        }

        if ((!isset($mission['application_deadline'])) && ((isset($mission['application_start_date']) &&
        ($mission['application_start_date'] !== null))
        && (isset($mission['application_end_date']) && ($mission['application_end_date'] !== null)) &&
         ($mission['application_end_date'] < $today || $mission['application_start_date'] > $today))) {
            $mission['set_view_detail'] = 1;
        }

        if ((isset($mission['application_start_time']) && ($mission['application_start_time'] !== null)) &&
         (isset($mission['application_end_time']) && ($mission['application_end_time'] !== null)) &&
         ($mission['application_end_time'] < $todayTime || $mission['application_start_time'] > $todayTime)) {
            $mission['set_view_detail'] = 1;
        }

        $mission['mission_rating_count'] = $mission['mission_rating_count'] ?
        round(2* $mission['mission_rating_count'])/2 : 0;

        if (!empty($mission['missionSkill']) && (isset($mission['missionSkill']))) {
            $returnData = [];
            foreach ($mission['missionSkill'] as $key => $value) {
                if ($value['skill']) {
                    $arrayKey = array_search($languageCode, array_column(
                        $value['skill']['translations'],
                        'lang'
                    ));
                    if ($arrayKey !== '') {
                        $returnData[config('constants.SKILL')][$key]['title'] =
                        $value['skill']['translations'][$arrayKey]['title'];
                        $returnData[config('constants.SKILL')][$key]['id'] =
                        $value['skill']['skill_id'];
                    }
                }
            }
            $returnData = array_map('array_values', $returnData);
            if (!empty($returnData)) {
                $mission[config('constants.SKILL')] = $returnData[config('constants.SKILL')];
            }
        }

        if (!empty($mission['organisation_detail']) && (isset($mission['organisation_detail']))
        && (is_array($mission['organisation_detail']))) {
            if ($mission['organisation_detail']) {
                $arrayKey = array_search($languageCode, array_column($mission['organisation_detail'], 'lang'));
                if ($arrayKey !== '') {
                    $mission['organisation_detail'] = $mission['organisation_detail'][$arrayKey]['detail'];
                }
            }
        }

        $mission['city_name'] = $mission['city']['name'];
        //Get city name from translation
        $cityTranslation = $mission['city']->languages->toArray();
        if ($cityTranslation) {
            $mission['city_name'] = $cityTranslation[0]['name'] ?? '';
            $cityTranslationkey = '';
            if (array_search($languageId, array_column($cityTranslation, 'language_id')) !== false) {
                $cityTranslationkey = array_search($languageId, array_column($cityTranslation, 'language_id'));
            }

            if ($cityTranslationkey !== '') {
                $mission['city_name'] = $cityTranslation[$cityTranslationkey]['name'];
            }
        }
        //set organization name
        if (!empty($mission['organization']) && (isset($mission['organization']))) {
            $mission['organisation_name'] = $mission['organization']['name'];
        }
        unset($mission['city']->languages);
        unset($mission['missionSkill']);

        // get mission tab transformation
        $missionTabDetails = $mission['missionTabs']->toArray();
        if ($missionTabDetails) {
            $missionTranslationsArray = [];
            foreach ($missionTabDetails as $missionTabKey => $missionTabValue) {
                $missionTranslationsArray['sort_key'] = $missionTabValue['sort_key'];
                $missionTranslationsArray['translations'] = [];
                if (isset($missionTabValue['get_mission_tab_detail'])) {
                    foreach ($missionTabValue['get_mission_tab_detail'] as $missionTabTranslationsValue) {
                        $languageCode = $tenantLanguages->where('language_id', $missionTabTranslationsValue['language_id'])->first()->code;
                        $missionTabTranslations['language_id'] = $missionTabTranslationsValue['language_id'];
                        $missionTabTranslations['language_code'] = $languageCode;
                        $missionTabTranslations['name'] = $missionTabTranslationsValue['name'];
                        $missionTabTranslations['section'] = json_decode($missionTabTranslationsValue['section']);
                        array_push($missionTranslationsArray['translations'], $missionTabTranslations);
                    }
                }
                $mission['missionTabs'][$missionTabKey] = $missionTranslationsArray;
            }
        }

        unset($mission['volunteeringAttribute']);
        return $mission;
    }
}
