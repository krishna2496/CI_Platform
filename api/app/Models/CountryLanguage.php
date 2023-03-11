<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CountryLanguage extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'country_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'country_language_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['country_language_id', 'country_id', 'language_id', 'language_code', 'name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['country_language_id', 'country_id', 'language_id', 'name'];
    
    /**
     * Store/update specified resource.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\CountryLanguage
     */
    public function createOrUpdateCountryLanguage(array $condition, array $data): CountryLanguage
    {
        return static::updateOrCreate($condition, $data);
    }
}
