<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\FooterPagesLanguage;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class FooterPage extends Model
{
    use SoftDeletes, CascadeSoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'footer_page';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'page_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['status', 'slug'];
    
    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['page_id', 'status', 'slug', 'sections', 'pageTranslations', 'pages'];

    /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['pageTranslations'];
    
    /**
     * Return the page's translations
     */
    public function pageTranslations(): HasMany
    {
        return $this->hasMany(FooterPagesLanguage::class, 'page_id', 'page_id');
    }
    
    /**
     * Soft delete the model from the database.
     *
     * @param  int $id
     * @return bool
     */
    public function deleteFooterPage(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }
    
    /**
     * Get the translations associated with the footer page.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pages(): HasMany
    {
        return $this->hasMany(FooterPagesLanguage::class, 'page_id', 'page_id');
    }
}
