<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CityLanguage extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'city_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'city_language_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['city_language_id', 'city_id', 'language_id', 'language_code', 'name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['city_language_id', 'city_id', 'language_id', 'name'];

    /**
     * Store/update specified resource.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\CityLanguage
     */
    public function createOrUpdateCityLanguage(array $condition, array $data): CityLanguage
    {
        return static::updateOrCreate($condition, $data);
    }
}
