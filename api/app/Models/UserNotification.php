<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserNotification extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_notification';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_notification_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['notification_id', 'user_notification_id', 'user_id', 'notification_type_id'];

    /**
     * Enable user notifications.
     *
     * @param  int  $userId
     * @param  int  $notificationTypeId
     * @return bool
     */
    public function enableUserNotification(int $userId, int $notificationTypeId): bool
    {
        return static::firstOrNew(array('user_id' => $userId, 'notification_type_id' => $notificationTypeId))->save();
    }
    
    /**
     * Disable user notifications.
     *
     * @param  int  $userId
     * @param  int  $notificationTypeId
     * @return bool
     */
    public function disableUserNotification(int $userId, int $notificationTypeId): bool
    {
        return static::where(array('user_id' => $userId, 'notification_type_id' => $notificationTypeId))->delete();
    }
}
