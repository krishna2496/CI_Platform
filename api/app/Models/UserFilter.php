<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFilter extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_filter';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_filter_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['filters', 'user_id','user_filter_id'];

    /**
     * The attributes that are visible.
     *
     * @var array
     */
    protected $visible = ['filters'];

    /**
     * Set description attribute on the model.
     *
     * @param  mixed $value
     * @return void
     */
    public function setFiltersAttribute($value) : void
    {
        $this->attributes['filters'] = json_encode($value,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $value
     * @return array
     */
    public function getFiltersAttribute($value): array
    {
        return json_decode($value, true);
    }

    /**
     * Store/update specified resource.
     *
     * @param  array $condition
     * @param  array $data
     * @return object
     */
    public function createOrUpdateUserFilter(array $condition, array $data): object
    {
        return static::updateOrCreate($condition, $data);
    }
}
