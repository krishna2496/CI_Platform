<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TenantSetting;

class TenantActivatedSetting extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_activated_setting';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'tenant_activated_setting_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tenant_setting_id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['tenant_setting_id', 'tenant_activated_setting_id', 'settings', 'setting_id'];

    /**
     * The rules that should validate request.
     *
     * @var array
     */
    public static $rules = [
        // Validation rules
    ];

    /**
     * Store/update settings.
     *
     * @param  int  $tenantSettingId
     * @param  int  $value
     * @return bool
     */
    public function storeSettings(int $tenantSettingId, int $value): bool
    {
        if ($value === 1) {
            return static::firstOrNew(array('tenant_setting_id' => $tenantSettingId))->save();
        } else {
            return static::where(['tenant_setting_id' => $tenantSettingId])->delete();
        }
    }

    /**
     * Fetch tenant settings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function settings(): hasOne
    {
        return $this->hasOne(TenantSetting::class, 'tenant_setting_id', 'tenant_setting_id');
    }
}
