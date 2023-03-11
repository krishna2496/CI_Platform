<?php
namespace App\Repositories\News;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\S3Helper;
use App\Models\News;
use App\Models\NewsLanguage;
use App\Models\NewsToCategory;
use App\Repositories\News\NewsInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NewsRepository implements NewsInterface
{
    /**
     * @var App\Models\News
     */
    private $news;

    /**
     * @var App\Models\NewsToCategory
     */
    private $newsToCategory;

    /**
     * @var App\Models\NewsLanguage
     */
    private $newsLanguage;

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
     * Create a new news repository instance.
     *
     * @param  App\Models\News $news
     * @param  App\Models\NewsToCategory $newsToCategory
     * @param  App\Models\NewsLanguage $newsLanguage
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @param  App\Helpers\Helpers $helpers
     * @param  App\Helpers\S3Helper $s3helper
     * @return void
     */
    public function __construct(
        News $news,
        NewsToCategory $newsToCategory,
        NewsLanguage $newsLanguage,
        LanguageHelper $languageHelper,
        Helpers $helpers,
        S3Helper $s3helper
    ) {
        $this->news = $news;
        $this->newsToCategory = $newsToCategory;
        $this->newsLanguage = $newsLanguage;
        $this->languageHelper = $languageHelper;
        $this->helpers = $helpers;
        $this->s3helper = $s3helper;
    }

    /**
     * Display news lists.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $languageId
     * @param string $newsStatus
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getNewsList(
        Request $request,
        string $newsStatus = null
    ): LengthAwarePaginator {
        $newsData = $this->news
        ->with(['newsToCategory' => function ($query) {
            $query->with(['newsCategory' => function ($query) {
                $query->select('news_category_id', 'category_name', 'translations');
            }]);
        }]);

        // Search filters for admin side
        if ($request->has('search')) {
            $newsData
            ->whereHas('newsLanguage', function ($query) use ($request) {
                $query->select('news_id', 'language_id', 'title', 'description')
                ->where('title', 'like', '%' . $request->input('search') . '%');
            })
            ->with(['newsLanguage' => function ($query) use ($request) {
                $query->select('news_id', 'language_id', 'title', 'description')
                ->where('title', 'like', '%' . $request->input('search') . '%');
            }]);
        } else {
            $newsData->with(['newsLanguage' => function ($query) {
                $query->select('news_id', 'language_id', 'title', 'description');
            }]);
        }

        // Order by filters for admin side
        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $newsData->orderBy('created_at', $orderDirection);
        }

        if ($newsStatus) {
            $newsData->where('status', $newsStatus);
        }

        return $newsData->paginate($request->perPage);
    }

    /**
     * Store news details.
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\News
     */
    public function store(Request $request): News
    {
        $newsArray = array(
            'user_name' => $request->user_name,
            'user_title' => $request->user_title,
            'user_thumbnail' => $request->user_thumbnail,
            'status' => $request->status
        );

        // Store news details
        $news = $this->news->create($newsArray);

        if ($request->has('news_image')) {
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            $newsId = $news->news_id;
            $newsImage = $this->s3helper->uploadFileOnS3Bucket(
                $request->news_image,
                $tenantName,
                "news/$newsId/images"
            );
            $news->update([
                'news_image' => $newsImage
            ]);
        }

        // Insert into news_to_category
        $newsToCategoryArray = array(
            'news_id' => $news->news_id,
            'news_category_id' => $request->news_category_id
        );
        $this->newsToCategory->create($newsToCategoryArray);

        // Insert into news_language
        if ($request->has('news_content')) {
            $languages = $this->languageHelper->getLanguages();
            $newsContent = $request->news_content;
            foreach ($newsContent['translations'] as $value) {
                // Get language_id from language code - It will fetch data from `ci_admin` database
                $language = $languages->where('code', $value['lang'])->first();

                $newsLanguageData = array('news_id' => $news->news_id,
                                        'language_id' => $language->language_id,
                                        'title' => $value['title'],
                                        'description' => $value['description']);

                $this->newsLanguage->create($newsLanguageData);
            }
        }
        return $news;
    }

    /**
     * Update news.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $newsId
     * @return App\Models\News
     */
    public function update(Request $request, int $newsId): News
    {
        $newsDetails = $this->news->findOrFail($newsId);

        // Update news details
        if ($request->has('news_image')) {
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            $newsImage = $this->s3helper->uploadFileOnS3Bucket(
                $request->news_image,
                $tenantName,
                "news/$newsId/images"
            );
            $request->request->add(['news_image' => $newsImage]);
        }
        $newsData = $newsDetails->update($request->toArray());

        // Update news_to_category
        if ($request->news_category_id) {
            $newsToCategoryArray = array(
                'news_category_id' => $request->news_category_id
                );
            $this->newsToCategory->where('news_id', $newsId)->update($newsToCategoryArray);
        }

        // Update into news_language
        if ($request->has('news_content')) {
            $languages = $this->languageHelper->getLanguages();
            $newsContent = $request->news_content;
            foreach ($newsContent['translations'] as $value) {
                // Get language_id from language code - It will fetch data from `ci_admin` database
                $language = $languages->where('code', $value['lang'])->first();

                $newsLanguageData = array('language_id' => $language->language_id,
                                        'title' => $value['title'],
                                        'description' => $value['description']
                                    );

                $this->newsLanguage->createOrUpdateNewsLanguage(['news_id' => $newsId,
                'language_id' => $language->language_id], $newsLanguageData);
            }
        }

        return $newsDetails;
    }

    /**
     * Get news details.
     *
     * @param int $id
     * @param string $newsStatus
     * @return App\Models\News
     */
    public function getNewsDetails(int $id, string $newsStatus = null): News
    {
        $newsQuery = $this->news
        ->with(['newsToCategory' => function ($query) {
            $query->with('newsCategory');
        }]);

        $newsQuery->with('newsLanguage');

        if ($newsStatus) {
            $newsQuery->where('status', $newsStatus);
        }

        return $newsQuery->findOrFail($id);
    }

    /**
     * Remove news.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $news = $this->news->findOrFail($id);
        return $news->delete();
    }

    /** Get news title
     *
     * @param int $newsId
     * @param int $languageId
     * @param int $defaultTenantLanguageId
     * @return string
     */
    public function getNewsTitle(int $newsId, int $languageId, int $defaultTenantLanguageId): string
    {
        $languageData = $this->newsLanguage->withTrashed()->select('title', 'language_id')
        ->where(['news_id' => $newsId])
        ->get();

        $title = '';

        if ($languageData->count() > 0) {
            $index = array_search($languageId, array_column($languageData->toArray(), 'language_id'));
            $language = ($index === false) ? $defaultTenantLanguageId : $languageId;
            $newsLanguage = $languageData->where('language_id', $language)->first();
            $title =  $newsLanguage->title;
        }

        return $title;
    }
}
