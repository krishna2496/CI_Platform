<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\FooterPage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterPagesLanguage extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'footer_pages_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['page_id', 'language_id', 'title', 'description'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['page_id', 'language_id', 'title', 'description', 'sections'];

    /**
     * Set description attribute on the model.
     *
     * @param  array $value
     * @return void
     */
    public function setDescriptionAttribute(array $value): void
    {
        $this->attributes['description'] = json_encode($value,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $value
     * @return array
     */
    public function getDescriptionAttribute(string $value): array
    {
        return json_decode($value,  true);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $value
     * @return array
     */
    public function getSectionsAttribute(string $value): array
    {
        return json_decode($value,  true);
    }

    /**
     * Store/update specified resource.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\FooterPagesLanguage
     */
    public function createOrUpdateFooterPagesLanguage(array $condition, array $data): FooterPagesLanguage
    {
        return static::updateOrCreate($condition, $data);
    }
}
