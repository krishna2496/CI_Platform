<?php
namespace App\Models;

use App\Models\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Notification extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'notification_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['notification_id', 'notification_type_id',
    'user_id', 'is_read', 'entity_id', 'action', 'is_email_notification'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['notification_id', 'notification_type_id',
    'user_id', 'is_read', 'entity_id', 'action', 'notificationType', 'created_at'];

    /**
     * Defined has one relation for the notification_type table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function notificationType(): HasOne
    {
        return $this->hasOne(NotificationType::class, 'notification_type_id', 'notification_type_id');
    }
}
