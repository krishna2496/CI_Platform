<?php
namespace App\Models;

use App\User;
use App\Models\Mission;
use App\Models\CityLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class City extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'city';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'city_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['city_id', 'country_id', 'name','translations', 'languages','state_id','state'];

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = ['city_id', 'country_id','state_id'];

    /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['languages'];
    
    /**
     * Get the city translation associated with the city.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function languages(): HasMany
    {
        return $this->hasMany(CityLanguage::class, 'city_id', 'city_id');
    }

    /**
     * Soft delete the model from the database.
     *
     * @param  int $id
     * @return bool
     */
    public function deleteCity(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }

    /**
     * Get the mission which belongs to City
     *
     * @return void
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class, 'city_id', 'city_id');
    }

    /**
     * Get the user which belongs to City
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'city_id', 'city_id');
    }

    /**
     * Get state associated with the citys.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function state()
    {
        return $this->hasOne(State::class, 'state_id', 'state_id');
    }

}
