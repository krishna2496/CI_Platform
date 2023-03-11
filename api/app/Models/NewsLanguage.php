<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsLanguage extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'news_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'news_language_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['news_id', 'language_id', 'title', 'description'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['news_id', 'language_id', 'title', 'description'];

    /**
     * Set description attribute on the model.
     *
     * @param  string $value
     * @return void
     */
    public function setDescriptionAttribute(string $value): void
    {
        $this->attributes['description'] = trim($value);
    }

    /**
     * Store/update news language.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\NewsLanguage
     */
    public function createOrUpdateNewsLanguage(array $condition, array $data): NewsLanguage
    {
        return static::updateOrCreate($condition, $data);
    }
}
