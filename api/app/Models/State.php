<?php
namespace App\Models;

use App\User;
use App\Models\Mission;
use App\Models\StateLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class State extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'state';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'state_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['state_id', 'country_id', 'name','translations', 'languages'];

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = ['state_id', 'country_id'];

    /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['languages'];

    /**
     * Get the state translation associated with the state.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function languages(): HasMany
    {
        return $this->hasMany(StateLanguage::class, 'state_id', 'state_id');
    }

    /**
     * Soft delete the model from the database.
     *
     * @param  int $id
     * @return bool
     */
    public function deleteState(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }

    /**
     * Get the mission which belongs to State
     *
     * @return void
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class, 'state_id', 'state_id');
    }

    /**
     * Get the user which belongs to State
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'state_id', 'state_id');
    }
}
