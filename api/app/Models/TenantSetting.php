<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class TenantSetting extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_setting';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'tenant_setting_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['setting_id'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['tenant_setting_id','setting_id'];

    /**
     * Fetch all tenant settings.
     *
     * @param array $ids
     *
     * @return Illuminate\Support\Collection
     */
    public function getAllTenantSettings(array $ids = []): Collection
    {
        return $this->select(
                'tenant_setting_id',
                'setting_id'
            )
            ->orderBy('setting_id')
            ->when(!empty($ids), function ($query) use ($ids) {
                return $query->whereIn('setting_id', $ids);
            })
            ->get();
    }
}
