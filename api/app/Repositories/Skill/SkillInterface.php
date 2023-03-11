<?php
namespace App\Repositories\Skill;

use Illuminate\Http\Request;
use App\Models\Skill;
use Illuminate\Pagination\LengthAwarePaginator;

interface SkillInterface
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $skill_id
     * @return \Illuminate\Http\Response
     */
    public function skillList(Request $request, String $skill_id = '');

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function skillDetails(Request $request): LengthAwarePaginator;

    /**
     * Store a newly created resource in storage.
     *
     * @param array $request
     * @return App\Models\Skill
     */
    public function store(array $request): Skill;

    /**
     * Update the specified resource in storage.
     *
     * @param  array  $request
     * @param  int  $id
     * @return App\Models\Skill
     */
    public function update(array $request, int $id): Skill;

    /**
     * Find specified resource in storage.
     *
     * @param  int  $id
     * @return App\Models\Skill
     */
    public function find(int $id): Skill;

    /**
     * Remove specified resource in storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;
}
