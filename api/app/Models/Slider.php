<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slider extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'slider';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'slider_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['slider_id', 'url', 'translations', 'sort_order'];

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = ['slider_id', 'url', 'translations', 'sort_order'];

    /**
     * Set translations attribute on the model.
     *
     * @param  array $value
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
     * Soft delete from the database.
     *
     * @param  int  $id
     * @return bool
     */
    public function deleteSlider(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }
}
