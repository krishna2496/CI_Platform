<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\User;

class UserCustomFieldValue extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_custom_field_value';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_custom_field_value_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['field_id', 'user_id', 'value'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['user_custom_field_value_id', 'field_id', 'user_id', 'value', 'userCustomField'];

    /**
     * Store/update specified resource.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\UserCustomFieldValue
     */
    public function createOrUpdateCustomFieldValue(array $condition, array $data): UserCustomFieldValue
    {
        return static::updateOrCreate($condition, $data);
    }

    /**
     * Defined has many relation for the user_custom_field table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userCustomField(): HasMany
    {
        return $this->hasMany(UserCustomField::class, 'field_id', 'field_id');
    }
}
