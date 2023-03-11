<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionLanguage extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_language_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'mission_id',
        'language_id',
        'title',
        'description',
        'objective',
        'short_description',
        'custom_information',
        'label_goal_achieved',
        'label_goal_objective'
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'lang',
        'language_id',
        'language_code',
        'title',
        'objective',
        'short_description',
        'description',
        'custom_information',
        'label_goal_achieved',
        'label_goal_objective'
    ];

    /**
     * Set description attribute on the model.
     *
     * @param array $value
     * @return void
     */
    public function setDescriptionAttribute(array $value)
    {
        $this->attributes['description'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get an attribute from the model.
     *
     * @param $value
     * @return array
     */
    public function getDescriptionAttribute($value): array
    {
        if ($value) {
            return json_decode($value, true);
        }

        return [];
    }

    /**
     * Store/update specified resource.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\MissionLanguage
     */
    public function createOrUpdateLanguage(array $condition, array $data): MissionLanguage
    {
        return static::updateOrCreate($condition, $data);
    }

    /**
     * Set custom conformation attribute on the model.
     *
     * @param $value
     * @return void
     */
    public function setCustomInformationAttribute($value)
    {
        $this->attributes['custom_information'] = isset($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
    }

    /**
     * Get an attribute from the model.
     *
     * @param $value
     * @return array
     */
    public function getCustomInformationAttribute($value)
    {
        if ($value) {
            return json_decode($value, true);
        }

        return [];
    }

    /**
     * Delete mission language.
     *
     * @param int $missionId
     * @param int $languageId
     * @return bool
     */
    public function deleteMissionLanguage(int $missionId, int $languageId): bool
    {
        return static::where(['mission_id'=> $missionId, 'language_id' => $languageId])->delete();
    }
}
