<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class UserDonationGoal extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_donation_goal';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_donation_goal_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['donation_goal', 'donation_goal_year'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_privacy_setting_id', 'user_id', 'donation_goal', 'donation_goal_year'];

    /**
     * Generate UUID string
     *
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->user_donation_goal_id = (string) Uuid::uuid4()->toString();
        });
    }
}
