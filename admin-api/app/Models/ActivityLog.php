<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_log';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'activity_log_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'action', 'object_class', 'object_id', 'object_value', 'date',
    'user_id', 'user_type', 'user_value'];

    /**
     * Set value in serialize form
     * @param array $value
     * @return void
     */
    public function setObjectValueAttribute(array $value = null)
    {
        if (!is_null($value)) {
            $this->attributes['object_value'] = serialize($value);
        }
    }
}
