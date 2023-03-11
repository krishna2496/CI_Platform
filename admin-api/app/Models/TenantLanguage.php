<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Language;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantLanguage extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'tenant_language_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tenant_id','language_id','default'];

    /**
     * The attributes that aappend in response.
     *
     * @var array
     */
    protected $appends = ['language_code'];

    /**
     * The attributes that are visible.
     *
     * @var array
     */
    protected $visible = ['language_id', 'default', 'language', 'name', 'code', 'tenant_language_id'];

    /**
    * Define a one-to-one relationship.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function language(): HasOne
    {
        return $this->hasOne(Language::class, 'language_id', 'language_id');
    }
     
    /**
     * Store/Update tenant language details.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\TenanLanguage
     */
    public function createOrUpdate(array $condition, array $data): TenantLanguage
    {
        return static::updateOrCreate($condition, $data);
    }

    /**
     * Reset tenant default language.
     *
     * @param  int  $tenantId
     * @return bool
     */
    public function resetDefaultTenantLanguage(int $tenantId)
    {
        return static::where(['tenant_id' => $tenantId, 'default' => config('constants.language_status.ACTIVE')])
        ->update(['default'=> config('constants.language_status.INACTIVE')]);
    }
}
