<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MissionImpactDonationLanguage extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_impact_donation_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_impact_donation_language_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'impact_donation_id,',
        'language_id',
        'content'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mission_impact_donation_language_id',
        'impact_donation_id',
        'language_id',
        'content'
    ];

    /**
     * Store/update specified resource for mission impact donation translations.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\MissionImpactDonationLanguage
     */
    public function createOrUpdateDonationImpactTranslation(
        array $condition,
        array $data
    ): MissionImpactDonationLanguage {
        return static::updateOrCreate($condition, $data);
    }
}
