<?php
namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'message_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */

    protected $visible = ['message_id', 'user_id', 'admin_name', 'subject', 'message', 'is_read',
        'is_anonymous','first_name','last_name','avatar','email', 'created_at', 'unread', 'sent_from'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'sent_from', 'admin_name', 'subject', 'message', 'is_read','is_anonymous'];

    /**
     * Defined has one relation for the user table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }
    
    /**
     * Find message.
     *
     * @param int $messageId
     * @param int $userId
     * @param int $sentFrom
     * @return App\Models\Message
     */
    public function findMessage(int $messageId, int $userId = null, int $sentFrom): Message
    {
        return $this->where([
            'message_id' => $messageId,
            'sent_from' => $sentFrom
            ])->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })->firstOrFail();
    }
}
