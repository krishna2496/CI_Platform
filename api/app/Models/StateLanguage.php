<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StateLanguage extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'state_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'state_language_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['state_language_id','language_id', 'language_code', 'name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['state_language_id', 'state_id', 'language_id', 'name'];

    /**
     * Store/update specified resource.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\StateLanguage
     */
    public function createOrUpdateStateLanguage(array $condition, array $data): StateLanguage
    {
        return static::updateOrCreate($condition, $data);
    }
}
