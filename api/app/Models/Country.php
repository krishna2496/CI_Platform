<?php
namespace App\Models;

use App\Models\City;
use App\User;
use App\Models\Mission;
use App\Models\CountryLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class Country extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'country';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'country_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['country_id', 'ISO', 'translations', 'languages'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['country_id', 'ISO'];

    /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['languages','city','state'];

    /**
     * Get languages associated with the country.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function languages()
    {
        return $this->hasMany(Countrylanguage::class, 'country_id', 'country_id');
    }

    /**
     * Soft delete the model from the database.
     *
     * @param  int $id
     * @return bool
     */
    public function deleteCountry(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }

    /**
     * Set ISO attribute on the model.
     *
     * @param $value
     * @return void
     */
    public function setISOAttribute($value)
    {
        $this->attributes['ISO'] = strtoupper($value);
    }

    /**
     * Get city associated with the country.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function city()
    {
        return $this->hasMany(City::class, 'country_id', 'country_id');
    }

    /**
     * Get the mission which belongs to City
     *
     * @return void
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class, 'country_id', 'country_id');
    }

    /**
     * Get the user which belongs to City
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'country_id', 'country_id');
    }

    /**
     * Get state associated with the country.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function state()
    {
        return $this->hasMany(State::class, 'country_id', 'country_id');
    }
}
