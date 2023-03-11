<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class NewsCategory extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'news_category';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'news_category_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['news_category_id', 'category_name', 'translations'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['category_name', 'translations'];

     /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['newsToCategory'];

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
        $data = @json_decode($value);
        return ($data !== null) ? json_decode($value, true) : [];
    }

    /**
     * Find news category by id.
     *
     * @param  int  $id
     * @return NewsCategory
     */
    public function findNewsCategory(int $id): NewsCategory
    {
        return static::findOrFail($id);
    }

    /**
     * Delete news category by id.
     *
     * @param  int  $id
     * @return bool
     */
    public function deleteNewsCategory(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }

    /**
     * Get the news to category record associated with the news category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function newsToCategory(): HasMany
    {
        return $this->hasMany(NewsToCategory::class, 'news_category_id', 'news_category_id');
    }
}
