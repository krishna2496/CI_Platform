<?php
namespace App\Repositories\Language;

use App\Repositories\Language\LanguageInterface;
use Illuminate\Http\Request;
use App\Models\Language;
use Illuminate\Pagination\LengthAwarePaginator;

class LanguageRepository implements LanguageInterface
{
    /**
     * @var App\Models\Language
     */
    private $language;

    /**
     * Create a new Language repository instance.
     *
     * @param App\Models\Language
     * @return void
     */
    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * Get listing of language
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getLanguageList(Request $request): LengthAwarePaginator
    {
        $languageQuery = $this->language;
		// Search filter
        if ($request->has('search')) {
            $languageQuery = $languageQuery->where('name', 'like', '%' . $request->search . '%');
        }
		
		// fetch only active languages
		if ($request->has('status') && $request->status == 'true') {
            $languageQuery = $languageQuery->where('status', '1');
        }
		
		// Order by filters
        if ($request->has('order')) {
            $languageQuery = $languageQuery->orderBy('created_at', $request->order);
        }

        return $languageQuery->paginate($request->perPage);
    }

    /**
     * Display language detail.
     *
     * @param  int  $id
     * @return App\Models\Language
     */
    public function find(int $id): Language
    {
        return $this->language->findOrFail($id);
    }

    /**
     * Store language data
     *
     * @param  array $languageData
     * @return App\Models\Language
     */
    public function store(array $languageData): Language
    {
        return $this->language->create($languageData);
    }

    /**
     * Update language details in storage.
     *
     * @param  array $languageData
     * @param  int  $id
     * @return App\Models\Language
     */
    public function update(array $languageData, int $id): Language
    {
        $languageDetails = $this->find($id);
        $languageDetails->update($languageData);
        return $languageDetails;
    }

    /**
     * Delete language by id.
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $languageData = $this->find($id);
        return $languageData->delete();
    }

    /**
     * It will check is language is belongs to any tenant or not
     *
     * @param int $id
     * @return bool
     */
    public function hasLanguage(int $id): bool
    {
        return $this->language->whereHas('language')->whereLanguageId($id)->count() ? true : false;
    }
}
