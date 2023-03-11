<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TenantOption;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Collection;

class TenantHasOption extends Model
{
    use SoftDeletes;

    /**
    * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_has_option';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'tenant_has_option_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tenant_option_id', 'tenant_id', 'option_value'];

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
    protected $visible = ['tenant_option_id', 'tenant_id', 'option_value', 'option'];

    /**
     * The rules that should validate request.
     *
     * @var array
     */
    public static $rules = [
        // Validation rules
    ];
}
