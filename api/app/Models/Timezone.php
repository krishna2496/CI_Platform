<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timezone extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'timezone';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'timezone_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['timezone_id', 'timezone', 'offset', 'status'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['timezone_id', 'timezone', 'offset', 'status'];
}
