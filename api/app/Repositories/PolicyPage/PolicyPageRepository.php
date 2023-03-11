<?php
namespace App\Repositories\PolicyPage;

use App\Repositories\PolicyPage\PolicyPageInterface;
use Illuminate\Http\Request;
use App\Models\PolicyPage;
use App\Models\PolicyPagesLanguage;
use App\Helpers\LanguageHelper;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PolicyPageRepository implements PolicyPageInterface
{
    /**
     * @var App\Models\PolicyPage
     */
    private $page;
    
    /**
     * @var App\Models\PolicyPagesLanguage
     */
    private $policyPageLanguage;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;
    
    /**
     * Create a new repository instance.
     *
     * @param App\Models\PolicyPage $page
     * @param App\Models\PolicyPagesLanguage $policyPageLanguage
     * @param App\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    public function __construct(
        PolicyPage $page,
        PolicyPagesLanguage $policyPageLanguage,
        LanguageHelper $languageHelper
    ) {
        $this->page = $page;
        $this->policyPageLanguage = $policyPageLanguage;
        $this->languageHelper = $languageHelper;
    }
    
    /**
     * Store a newly created resource in storage
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\PolicyPage
     */
    public function store(Request $request): PolicyPage
    {
        $postData = $request->page_details;
        // Set data for create new record
        $page = array();
        $page['status'] = config('constants.ACTIVE');
        $page['slug'] = $postData['slug'];
        // Create new policy page
        $policyPage = $this->page->create($page);
        
        $languages = $this->languageHelper->getLanguages();
        
        foreach ($postData['translations'] as $value) {
            // Get language_id from language code - It will fetch data from `ci_admin` database
            $language = $languages->where('code', $value['lang'])->first();
            $policyPageLanguageData = array('page_id' => $policyPage['page_id'],
                                      'language_id' => $language->language_id,
                                      'title' => $value['title'],
                                      'description' => $value['sections']);
                                      
            $this->policyPageLanguage->create($policyPageLanguageData);
            
            unset($policyPageLanguageData);
        }
        return $policyPage;
    }
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request $request
    * @param  int $id
    * @return App\Models\PolicyPage
    */
    public function update(Request $request, int $id): PolicyPage
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
        
        // Update policy page
        $policyPage = $this->page->findOrFail($id);
        $policyPage->update($page);
        
        $languages = $this->languageHelper->getLanguages();
                 
        if (isset($postData['translations'])) {
            foreach ($postData['translations'] as $value) {
                $language = $languages->where('code', $value['lang'])->first();
                $pageLanguageData = [
                    'title' => $value['title'],
                    'description' => $value['sections'],
                    'page_id' => $policyPage['page_id'],
                    'language_id' => $language->language_id
                ];

                $this->policyPageLanguage->createOrUpdatePolicyPagesLanguage(['page_id' => $id,
                 'language_id' => $language->language_id], $pageLanguageData);
                unset($pageLanguageData);
            }
        }
        return $policyPage;
    }
    
    /**
    * Display a listing of policy pages.
    *
    * @param Illuminate\Http\Request $request
    * @return Illuminate\Pagination\LengthAwarePaginator
    */
    public function getPolicyPageList(Request $request): LengthAwarePaginator
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
     * @return App\Models\PolicyPage
     */
    public function find(int $id): PolicyPage
    {
        return $this->page->with('pages')->findOrFail($id);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->page->deletePolicyPage($id);
    }

    /**
     * Get a listing of resource.
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Support\Collection
     */
    public function getPageList(Request $request): Collection
    {
        $pageQuery = $this->page->with(['pages' => function ($query) {
            $query->select('page_id', 'language_id', 'title');
        }]);
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
     * @param Illuminate\Http\Request $request
     * @param string $slug
     * @return App\Models\PolicyPage
     */
    public function getPageDetail(Request $request, string $slug): PolicyPage
    {
        $language = $this->languageHelper->getLanguageDetails($request);
        $languageId = $language->language_id;
        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
        $defaultTenantLanguageId = $defaultTenantLanguage->language_id;

        $policyPage = $this->page->with(['pages' => function ($query) {
            $query->select('page_id', 'language_id', 'title', 'description as sections');
        }])->whereSlug($slug)->firstorfail();
        
        $key = array_search($languageId, array_column($policyPage['pages']->toArray(), 'language_id'));
        $language = ($key === false) ? $defaultTenantLanguageId : $languageId;
        $pages[] = $policyPage['pages']->where('language_id', $language)->first();
        unset($policyPage['pages']);
        $policyPage['pages'] = $pages;
        return $policyPage;
    }
}
