<?php
namespace App\Repositories\Skill;

use Illuminate\Http\Request;
use App\Models\Skill;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class SkillRepository implements SkillInterface
{
    /**
     * @var App\Models\Skill
     */
    public $skill;

    /**
     * Create a new Mission repository instance.
     *
     * @param  App\Models\Skill $skill
     * @return void
     */
    public function __construct(Skill $skill)
    {
        $this->skill = $skill;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param string $skill_id
     * @return \Illuminate\Http\Response
     */
    public function skillList(Request $request, string $skill_id = '')
    {
        $skillQuery = $this->skill->select('skill_name', 'skill_id', 'translations');
        if ($skill_id !== '') {
            $skillQuery->whereIn("skill_id", explode(",", $skill_id));
        }
        $skill = $skillQuery->get();
        return $skill;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function skillDetails(Request $request): LengthAwarePaginator
    {
        $searchLanguage = $request->searchLanguage;
        $sortBy = $request->get('sortBy');

        if ($sortBy && $sortBy === 'translations' && $searchLanguage) {
            $skillQuery = $this->skill->select('skill_id', 'skill_name', 'translations', 'parent_skill', 'created_at', 'updated_at', DB::raw("JSON_EXTRACT(translations, '$." . $searchLanguage . "') COLLATE utf8mb4_unicode_ci AS translated"));
            $sortBy = 'translated';
        } else {
            $skillQuery = $this->skill->select('skill_id', 'skill_name', 'translations', 'parent_skill', 'created_at', 'updated_at');
        }

        if ($request->has('id')) {
            $skillQuery = $skillQuery->whereIn('skill_id', $request->get('id'));
        }

        $skillQuery->when($request->has('search'), function ($query) use ($request, $searchLanguage) {
            $query->where('skill_name', 'like', $request->search.'%');

            $searchLanguage
                ? $query->orWhere(DB::raw("lower(json_unquote(json_extract(translations, '$.".$searchLanguage."')))"), 'LIKE', '%'. strtolower( $request->search ).'%')
                : $query->orWhere('translations', 'like', '%' . $request->search . '%');

        })->when($request->has('translations'), function ($query) use ($request) {
            /*
             * Filtering on translations
             * The regex here verifies that we have a translation (so no empty string)
             * for the given language codes passed in the key 'translations' of the $request
             */
            $query->where(function ($query) use ($request) {
                foreach ($request->translations as $languageCode) {
                    // Regex searches in translations column if the translation in the $languageCode exists and its length is greater than 0
                    $query->whereNotNull('translations->' . $languageCode)
                        ->where('translations->' . $languageCode , '!=', '');
                }
            });
        });

        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $skillQuery = $skillQuery->orderBy('skill_id', $orderDirection);
        } elseif ($request->has('sortBy') && $request->has('sortDir')) {
            $sortDir = $request->get('sortDir');
            $skillQuery = $skillQuery->orderBy($sortBy, $sortDir);
        }

        if ($request->has('limit') && $request->has('offset')) {
            $limit = $request->input('limit');
            $offset = $request->input('offset');

            return $skillQuery->paginate($limit, ['*'], 'page', round($offset / $limit) + 1);
        }

        return $skillQuery->paginate($request->perPage);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $request
     * @return App\Models\Skill
     */
    public function store(array $request): Skill
    {
        $request['parent_skill'] = $request['parent_skill'] ?? 0;
        return $this->skill->create($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  array  $request
     * @param  int  $id
     * @return App\Models\Skill
     */
    public function update(array $request, int $id): Skill
    {
        try {
            $skill = $this->skill->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException(trans('messages.custom_error_message.ERROR_SKILL_NOT_FOUND'));
        }
        $skill->update($request);
        return $skill;
    }

    /**
     * Find specified resource in storage.
     *
     * @param  int  $id
     * @return App\Models\Skill
     */
    public function find(int $id): Skill
    {
        return $this->skill->findSkill($id);
    }

    /**
     * Remove specified resource in storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->skill->deleteSkill($id);
    }

    /**
     * It will check is skill belongs to any mission or not
     *
     * @param int $id
     * @return bool
     */
    public function hasMissionSkill(int $id): bool
    {
        return $this->skill->whereHas('missionSkill')->whereSkillId($id)->count() ? true : false;
    }

    /**
     * It will check is skill belongs to any user or not
     *
     * @param int $id
     * @return bool
     */
    public function hasUserSkill(int $id): bool
    {
        return $this->skill->whereHas('userSkill')->whereSkillId($id)->count() ? true : false;
    }
}
