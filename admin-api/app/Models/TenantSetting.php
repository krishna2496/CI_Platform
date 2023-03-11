<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TenantHasSetting;

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
    protected $fillable = ['title', 'description', 'key'];

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
    protected $visible = ['tenant_setting_id', 'title', 'description', 'key', 'is_active'];

    /**
     * The rules that should validate request.
     *
     * @var array
     */
    public static $rules = [
        // Validation rules
    ];
}
