<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\NewsCategory;

class NewsToCategory extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'news_to_category';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'news_to_category_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['news_id', 'news_category_id'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['news_id', 'news_category_id', 'newsCategory'];

    /**
     * Get news category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function newsCategory(): HasMany
    {
        return $this->hasMany(NewsCategory::class, 'news_category_id', 'news_category_id');
    }
}
