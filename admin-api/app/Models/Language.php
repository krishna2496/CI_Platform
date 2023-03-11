<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

class Language extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'language_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','code','status'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['language_id', 'name', 'code', 'status'];

    /**
     * Defined has one relation for the tenanat langauge
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tenantLanguage(): HasMany
    {
        return $this->hasMany(TenantLanguage::class, 'language_id', 'language_id');
    }

    /**
     * Get the tenant language which belongs to Language
     *
     * @return void
     */
    public function language()
    {
        return $this->belongsTo(TenantLanguage::class, 'language_id', 'language_id');
    }
}
