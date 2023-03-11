<?php
namespace App\Transformations;

use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

trait StoryTransformable
{
    /**
     * Get Transfomed stories
     *
     * @param App\Models\Story $story
     * @param int $languageId
     * @param string $defaultAvatar
     * @return App\Models\Story
     */

    protected function transformStory(Story $story, int $languageId, string $defaultAvatar):Story
    {
        $storyData = new Story;
        $storyData->story_id = (int) $story->story_id;
        $storyData->mission_id = $story->mission_id;
        $storyData->title = $story->title;
        $storyData->description = $story->description;
        $storyData->story_visitor_count = (int) $story->story_visitor_count;
        $storyData->status = trans('general.status.' . $story->status);
        $storyData->published_at = $story->published_at;

        if (!empty($story->user)) {
            $storyData->user_id = $story->user_id;
            $storyData->first_name = $story->user->first_name;
            $storyData->last_name = $story->user->last_name;
            $storyData->avatar = !empty($story->user->avatar) ? $story->user->avatar : $defaultAvatar;
            $storyData->profile_text = $story->user->profile_text;
            $storyData->why_i_volunteer = $story->user->why_i_volunteer;
            $storyData->city = $story->user->city;
            $storyData->country = $story->user->country;
        }

        if (!empty($story->storyMedia)) {
            $storyData->storyMedia = $story->storyMedia;
        }


        $key = array_search($languageId, array_column($storyData->mission->missionLanguage->toArray(), 'language_id'));
        $language = ($key === false) ? 'en' : $languageId;
        $missionLanguage = $storyData->mission->missionLanguage->where('language_id', $language)->first();

        if (!is_null($missionLanguage)) {
            $storyData->mission_title = $missionLanguage->title;
        }

        return $storyData;
    }

    /**
     * Used for transform user stories
     *
     * @param Object $stories
     * @param App\Models\Story $storyStatusCount
     * @return array
     */
    protected function transformUserStories(Object $stories, Story $storyStatusCount): array
    {
        $transformedUserStories = array();

        $draftStories = $publishedStories = $pendingStories = $declinedStories = 0;

        foreach ($stories as $story) {
            $statusFlag = strtolower($story->status);
            $transformedUserStories['story_data'][] = [
                'story_id' => (int) $story->story_id,
                'mission_id' => $story->mission_id,
                'title' => $story->title,
                'description' => strip_tags($story->description),
                'status' => trans('general.status.' . $story->status),
                'storyMedia' => $story->storyMedia->first(),
                'created' => Carbon::parse($story->created_at)->format('d/m/Y'),
                'status_flag' => ucfirst($statusFlag)
            ];
        }
        if (count($stories) > 0) {
            $transformedUserStories['stats']['draft'] = $storyStatusCount->draft;
            $transformedUserStories['stats']['published'] =  $storyStatusCount->published;
            $transformedUserStories['stats']['pending'] =  $storyStatusCount->pending;
            $transformedUserStories['stats']['declined'] =  $storyStatusCount->declined;
        }

        return $transformedUserStories;
    }

    /**
     * Used for transform published stories
     *
     * @param Object $story
     * @param string $defaultAvatar
     * @return array
     */
    protected function transformPublishedStory(Object $story, string $defaultAvatar): array
    {
        $transformedPublishedStories = [];
        $languageCode = config('app.locale');
        foreach ($story as $storyData) {
            // get the theme name based on language set
            $themeName = $storyData->mission->missionTheme->theme_name;
            $arrayKey = array_search($languageCode, array_column(
                $storyData->mission->missionTheme['translations'],
                'lang'
            ));
            if ($arrayKey  !== false) {
                $themeName = $storyData->mission->missionTheme['translations'][$arrayKey]['title'];
            }

            $transformedPublishedStories [] = [
                    'story_id' => (int) $storyData->story_id,
                    'mission_id' => $storyData->mission_id,
                    'user_id' => $storyData->user_id,
                    'user_first_name' => $storyData->user->first_name,
                    'user_last_name' => $storyData->user->last_name,
                    'user_avatar' => !empty($storyData->user->avatar) ? $storyData->user->avatar : $defaultAvatar,
                    'title' => $storyData->title,
                    'description' => strip_tags($storyData->description),
                    'status' => trans('general.status.'.$storyData->status),
                    'storyMedia' => $storyData->storyMedia->first(),
                    'published_at' =>  Carbon::parse($storyData->published_at)->format('d/m/Y'),
                    'theme_name' => $themeName
            ];
        }

        return $transformedPublishedStories;
    }

    /**
     * Get Transfomed story details
     *
     * @param App\Models\Story $story
     * @param int $storyViewCount
     * @param string $defaultAvatar
     * @param int $languageId
     * @return Array
     */
    protected function transformStoryDetails(
        Story $story,
        int $storyViewCount,
        string $defaultAvatar,
        int $languageId
    ):array {

        $storyData['story_id'] = (int) $story->story_id;
        $storyData['mission_id'] = $story->mission_id;
        $storyData['title'] = $story->title;
        $storyData['description'] = $story->description;
        $storyData['story_visitor_count'] = $storyViewCount;
        $storyData['status'] = trans('general.status.' . $story->status);
        $storyData['published_at'] = $story->published_at;

        $cityTranslation = $story->user->city ? $story->user->city->languages->toArray() : [];
        $countryTranslation = $story->user->country->languages->toArray();

        $cityTranslationKey = $countryTranslationKey = $cityName = $countryName = '';
        $cityArray = [
            'name' => '',
        ];
        $countryArray = [
            'name' => '',
        ];
        if (array_search($languageId, array_column($cityTranslation, 'language_id')) !== false) {
            $cityTranslationKey = array_search($languageId, array_column($cityTranslation, 'language_id'));
        }

        if (array_search($languageId, array_column($countryTranslation, 'language_id')) !== false) {
            $countryTranslationKey = array_search($languageId, array_column($countryTranslation, 'language_id'));
        }

        if ($cityTranslationKey !== '' && $story->user->city) {
            $cityName = $cityTranslation[$cityTranslationKey]['name'];
        }

        $cityArray['name'] = $cityName;
        if ($countryTranslationKey !== '' && $story->user->country) {
            $countryName = $countryTranslation[$countryTranslationKey]['name'];
        }

        $countryArray['name'] = $countryName;
        $story->user->city = (object) $cityArray;
        $story->user->country = (object) $countryArray;
        if (!empty($story->user)) {
            $storyData['user_id'] = $story->user_id;
            $storyData['first_name'] = $story->user->first_name;
            $storyData['last_name'] = $story->user->last_name;
            $storyData['linked_in_url'] = $story->user->linked_in_url ?? '';
            $storyData['avatar'] = !empty($story->user->avatar) ? $story->user->avatar : $defaultAvatar;
            $storyData['profile_text'] = $story->user->profile_text;
            $storyData['why_i_volunteer'] = $story->user->why_i_volunteer;
            $storyData['city'] = $story->user->city;
            $storyData['country'] = $story->user->country;
        }

        if (!empty($story->storyMedia)) {
            $storyData['storyMedia'] = $story->storyMedia;
        }
        return $storyData;
    }
}
