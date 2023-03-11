<?php
namespace App\Repositories\Language;

use Illuminate\Http\Request;
use App\Models\Language;
use Illuminate\Pagination\LengthAwarePaginator;

interface LanguageInterface
{

    /**
     * Get listing of languages
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getLanguageList(Request $request): LengthAwarePaginator;

    /**
     * Display language detail.
     *
     * @param  int  $id
     * @return App\Models\Language
     */
    public function find(int $id): Language;

    /**
     * Store language data
     *
     * @param  array $languageData
     * @return App\Models\Language
     */
    public function store(array $languageData): Language;

    /**
     * Update language details in storage.
     *
     * @param  array $languageData
     * @param  int  $id
     * @return App\Models\Language
     */
    public function update(array $languageData, int $id): Language;
    
    /**
     * Delete language by id.
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
