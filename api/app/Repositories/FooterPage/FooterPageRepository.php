<?php
namespace App\Repositories\FooterPage;

use App\Repositories\FooterPage\FooterPageInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\FooterPage;
use App\Models\FooterPagesLanguage;
use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class FooterPageRepository implements FooterPageInterface
{
    /**
     * @var App\Models\FooterPage
     */
    private $page;

    /**
     * @var App\Models\FooterPagesLanguage
     */
    private $footerPageLanguage;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * Create a new repository instance.
     *
     * @param App\Models\FooterPage $page
     * @param App\Models\FooterPagesLanguage $footerPageLanguage
     * @param App\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    public function __construct(
        FooterPage $page,
        FooterPagesLanguage $footerPageLanguage,
        LanguageHelper $languageHelper
    ) {
        $this->page = $page;
        $this->footerPageLanguage = $footerPageLanguage;
        $this->languageHelper = $languageHelper;
    }

    /**
     * Store a newly created resource in storage
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\FooterPage
     */
    public function store(Request $request): FooterPage
    {
        $postData = $request->page_details;
        // Set data for create new record
        $page = array();
        $page['status'] = config('constants.ACTIVE');
        $page['slug'] = $postData['slug'];
        // Create new cms page
        $footerPage = $this->page->create($page);

        $languages = $this->languageHelper->getLanguages();
        foreach ($postData['translations'] as $value) {
            // Get language_id from language code - It will fetch data from `ci_admin` database
            $language = $languages->where('code', $value['lang'])->first();

            $footerPageLanguageData = array('page_id' => $footerPage['page_id'],
                                      'language_id' => $language->language_id,
                                      'title' => $value['title'],
                                      'description' => $value['sections']);

            $this->footerPageLanguage->create($footerPageLanguageData);

            unset($footerPageLanguageData);
        }
        return $footerPage;
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return App\Models\FooterPage
    */
    public function update(Request $request, int $id): FooterPage
    {
        $postData = $request->page_details;

        // Set data for update record
        $page = array();
        if (isset($postData['status'])) {
            $page['status'] = $postData['status'];
        }
        if (isset($postData['slug'])) {
            $page['slug'] = $postData['slug'];
        }

        // Update footer page
        $footerPage = $this->page->findOrFail($id);
        $footerPage->update($page);

        $languages = $this->languageHelper->getLanguages();

        if (isset($postData['translations'])) {
            foreach ($postData['translations'] as $value) {
                $language = $languages->where('code', $value['lang'])->first();
                $pageLanguageData = [
                    'title' => $value['title'],
                    'description' => $value['sections'],
                    'page_id' => $footerPage['page_id'],
                    'language_id' => $language->language_id
                ];

                $this->footerPageLanguage->createOrUpdateFooterPagesLanguage(['page_id' => $id,
                 'language_id' => $language->language_id], $pageLanguageData);
                unset($pageLanguageData);
            }
        }
        return $footerPage;
    }

    /**
    * Display a listing of footer pages.
    *
    * @param Illuminate\Http\Request $request
    * @return Illuminate\Pagination\LengthAwarePaginator
    */
    public function footerPageList(Request $request): LengthAwarePaginator
    {
        $pageQuery = $this->page->with('pageTranslations');

        if ($request->has('search')) {
            $pageQuery->wherehas('pageTranslations', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->input('search') . '%');
                $q->orWhere('description', 'like', '%' . $request->input('search') . '%');
            });
        }
        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $pageQuery->orderBy('page_id', $orderDirection);
        }

        return $pageQuery->paginate($request->perPage);
    }

    /**
     * Find the specified resource from database
     *
     * @param int $id
     * @return App\Models\FooterPage
     */
    public function find(int $id): FooterPage
    {
        return $this->page->with('pages')->findOrFail($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->page->deleteFooterPage($id);
    }

    /**
     * Get a listing of resource.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Support\Collection
     */
    public function getPageList(Request $request): Collection
    {
        $pageQuery = $this->page->with(['pages:page_id,language_id,title']);
        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $pageQuery->orderBy('page_id', $orderDirection);
        }
        $pageList = $pageQuery->get();

        $language = $this->languageHelper->getLanguageDetails($request);
        $languageId = $language->language_id;
        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
        $defaultTenantLanguageId = $defaultTenantLanguage->language_id;
        foreach ($pageList as $list) {
            $key = array_search($languageId, array_column($list['pages']->toArray(), 'language_id'));
            $language = ($key === false) ? $defaultTenantLanguageId : $languageId;
            $pages[] = $list['pages']->where('language_id', $language)->first();

            unset($list['pages']);
            $list['pages'] = $pages;
            unset($pages);
        }
        return $pageList;
    }

    /**
    * Get a listing of resource.
    *
    * @return Illuminate\Support\Collection
    */
    public function getPageDetailList(): Collection
    {
        return $this->page->with(['pages:page_id,language_id,title,description as sections'])->get();
    }

    /**
     * Get a listing of resource.
     *
     * @param Illuminate\Http\Request $request
     * @param string $slug
     * @return App\Models\FooterPage
     */
    public function getPageDetail(Request $request, string $slug): FooterPage
    {
        $language = $this->languageHelper->getLanguageDetails($request);
        $languageId = $language->language_id;
        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
        $defaultTenantLanguageId = $defaultTenantLanguage->language_id;

        $footerPage = $this->page->with(['pages:page_id,language_id,title,description as sections'])
            ->whereSlug($slug)->firstorfail();

        $key = array_search($languageId, array_column($footerPage['pages']->toArray(), 'language_id'));
        $language = ($key === false) ? $defaultTenantLanguageId : $languageId;
        $pages[] = $footerPage['pages']->where('language_id', $language)->first();
        unset($footerPage['pages']);
        $footerPage['pages'] = $pages;

        return $footerPage;
    }
}
