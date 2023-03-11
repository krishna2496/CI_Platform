<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UserCustomFieldValue;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class UserCustomField extends Model
{
    use SoftDeletes, CascadeSoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_custom_field';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'field_id';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = ['order', 'name', 'type', 'translations', 'is_mandatory', 'internal_note'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['field_id', 'order', 'name', 'type', 'translations', 'is_mandatory', 'internal_note'];
    
    /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['userCustomFieldValue'];

    /**
     * Set translations attribute on the model.
     *
     * @param  mixed $value
     * @return void
     */
    public function setTranslationsAttribute(array $value): void
    {
        $this->attributes['translations'] = json_encode($value,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $value
     * @return array
     */
    public function getTranslationsAttribute(string $value): array
    {
        return json_decode($value, true);
    }

    /**
     * Delete the specified resource.
     *
     * @param  int $id
     * @return bool
     */
    public function deleteCustomField(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }

    /**
     * Defined has one relation for the user custom field value
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userCustomFieldValue(): HasMany
    {
        return $this->hasMany(UserCustomFieldValue::class, 'field_id', 'field_id');
    }
}
