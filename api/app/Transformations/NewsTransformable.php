<?php
namespace App\Transformations;

use App\Models\News;

trait NewsTransformable
{
    /**
     * Get transformed news
     *
     * @param App\Models\News $news
     * @param bool $sortDescription
     * @param int $languageId
     * @param int $defaultTenantLanguage
     * @param $languageCode
     * @param $defaultTenantLanguageCode
     * @return array
     */
    protected function getTransformedNews(
        News $news,
        bool $sortDescription = null,
        int $languageId = null,
        int $defaultTenantLanguage = null,
        $languageCode = null,
        $defaultTenantLanguageCode = null
    ): array {
        $newsDetails = $news->toArray();
        $wordLimit = config('constants.NEWS_SHORT_DESCRIPTION_WORD_LIMIT');
                        
        $transformedNews = array();
        $transformedNews['news_id'] = $newsDetails['news_id'];
        $transformedNews['news_image'] = $newsDetails['news_image'];
        $transformedNews['user_name'] = $newsDetails['user_name'];
        $transformedNews['user_title'] = $newsDetails['user_title'];
        $transformedNews['user_thumbnail'] = $newsDetails['user_thumbnail'];
        $transformedNews['published_on'] = $newsDetails['created_at'];
        $transformedNews['status'] = $newsDetails['status'];

        if (isset($newsDetails['news_language']) && !empty($newsDetails['news_language'])) {
            $newsContent = [];
            if (is_null($languageId)) {
                foreach ($newsDetails['news_language'] as $key => $value) {
                    $newsContent[$key]['language_id'] = $value['language_id'];
                    $newsContent[$key]['title'] = $value['title'];
                    $newsContent[$key]['description'] = ($sortDescription) ?
                    strip_tags($value['description']) : $value['description'];
                }
            } else {
                $key = array_search($languageId, array_column($news['newsLanguage']->toArray(), 'language_id'));
                $language = ($key === false) ? $defaultTenantLanguage : $languageId;
                $newsLanguage = $news['newsLanguage']->where('language_id', $language)->first();

                $description =$newsLanguage->description;
                $newsContent['language_id'] = $newsLanguage->language_id;
                $newsContent['title'] = $newsLanguage->title;
                $newsContent['description'] = ($sortDescription) ?
                strip_tags($description) : $description;
            }
            $transformedNews['news_content'] = $newsContent;
        }
        $newsCategory = [];
        if (isset($newsDetails['news_to_category'])) {
            $newsCategoryArray = array();
            foreach ($newsDetails['news_to_category'] as $key => $value) {
                if (is_null($languageId)) {
                    $transformedNews['news_category_id'] = $newsDetails['news_to_category'][$key]['news_category_id'];
                } else {
                    $newsCategoryArray[$key]['news_category_id'] = $value['news_category_id'];
                    foreach ($newsDetails['news_to_category'][$key]['news_category'] as $category) {
                        $index = array_search($languageCode, array_column(
                            $category['translations'],
                            'lang'
                        ));
                    
                        if ($index  !== false) {
                            $newsCategory[] = $category['translations'][$index]['title'];
                        } else {
                            $index = array_search($defaultTenantLanguageCode, array_column(
                                $category['translations'],
                                'lang'
                            ));
                            $newsCategory[] = ($index !== false) ? $category['translations'][$index]['title'] : '';
                        }
                        unset($category['translations']);
                    }
                    $transformedNews['news_category'] = $newsCategory;
                }
            }
        }
        return $transformedNews;
    }
}
