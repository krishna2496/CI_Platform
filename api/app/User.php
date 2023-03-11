<?php
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordInterface;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use App\Models\City;
use App\Models\Country;
use App\Models\Timezone;
use App\Models\Availability;
use App\Models\UserCustomFieldValue;
use App\Models\Timesheet;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nicolaslopezj\Searchable\SearchableTrait;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\FavouriteMission;
use App\Models\Message;
use App\Models\MissionApplication;
use App\Models\MissionInvite;
use App\Models\MissionRating;
use App\Models\Story;
use App\Models\StoryInvite;
use App\Models\UserFilter;
use App\Models\UserNotification;
use App\Models\UserSkill;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordInterface
{
    use Authenticatable, Authorizable, CanResetPasswordTrait, Notifiable, SoftDeletes, SearchableTrait,
    CascadeSoftDeletes;

    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "user";

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = "user_id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar',
        'timezone_id',
        'availability_id',
        'why_i_volunteer',
        'employee_id',
        'department',
        'position',
        'city_id',
        'country_id',
        'profile_text',
        'linked_in_url',
        'status',
        'language_id',
        'title',
        'hours_goal',
        'is_profile_complete',
        'receive_email_notification',
        'expiry',
        'invitation_sent_at',
        'pseudonymize_at',
        'currency',
        'is_admin',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar',
        'timezone_id',
        'availability_id',
        'why_i_volunteer',
        'employee_id',
        'department',
        'position',
        'city_id',
        'country_id',
        'profile_text',
        'linked_in_url',
        'status',
        'title',
        'city',
        'country',
        'timezone',
        'language_id',
        'availability',
        'userCustomFieldValue',
        'cookie_agreement_date','hours_goal',
        'skills',
        'is_profile_complete',
        'receive_email_notification',
        'messages_count',
        'comments_count',
        'stories_count',
        'stories_views_count',
        'stories_invited_users_count',
        'first_login',
        'last_login',
        'last_volunteer',
        'open_volunteer_request',
        'mission',
        'favourite_mission',
        'hours_goal',
        'expiry',
        'invitation_sent_at',
        'pseudonymize_at',
        'currency'
    ];

     /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['FavouriteMission','Message','missionApplication',
    'missionInviteFromUserId','missionInviteToUserId','notification','missionRating',
    'timesheet','userCustomFieldValue','userFilter','userNotification','userSkill'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'is_admin',
    ];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'user.first_name' => 10,
            'user.last_name' => 10,
            'user.email' => 10
        ]
    ];

    /**
    * Defined has one relation for the city table.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function city(): HasOne
    {
        return $this->hasOne(City::class, 'city_id', 'city_id');
    }

    /**
    * Defined has one relation for the country table.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'country_id', 'country_id');
    }

    /**
    * Defined has one relation for the country table.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function availability(): HasOne
    {
        return $this->hasOne(Availability::class, 'availability_id', 'availability_id');
    }

    /**
    * Defined has one relation for the timezone table.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function timezone(): HasOne
    {
        return $this->hasOne(Timezone::class, 'timezone_id', 'timezone_id');
    }

    /**
     * Defined has many relation for the user_custom_field_value table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userCustomFieldValue(): HasMany
    {
        return $this->hasMany(UserCustomFieldValue::class, 'user_id', 'user_id');
    }

    /**
     * The is set attribute method for password. This will make has of entered password, before insert.
     *
     * @param string $password
     * @return void
     */
    public function setPasswordAttribute(string $password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Find the specified resource.
     *
     * @param  int  $id
     * @return array
     */
    public function findUser(int $id)
    {
        return static::with('city', 'country', 'timezone', 'userCustomFieldValue.userCustomField', 'skills.skill')->findOrFail($id);
    }

    /**
     * Delete the specified resource.
     *
     * @param  int  $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }

    /**
     * Search user
     *
     * @param string $term
     * @param int $userId
     *
     * @return Collection
     */
    public function searchUser(string $term, int $userId)
    {
        return $this->where('user_id', '<>', $userId)
            ->where('status', '=', '1')
            ->where(function ($query) {
                $query->whereNull('expiry')
                    ->orWhere('expiry', '>', (new \DateTimeImmutable())->format(self::DATETIME_FORMAT));
            })
            ->search($term);
    }

    /**
     * Search user
     *
     * @param string $email
     * @return mixed
     */
    public function getUserByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get user detail
     *
     * @param int $userId
     * @return App\User
     */
    public function findUserDetail(int $userId): User
    {
        return static::with('city', 'country', 'timezone', 'availability', 'userCustomFieldValue', 'skills.skill')->findOrFail($userId);
    }

    /**
     * Get specified resource.
     *
     * @param int $userId
     * @return null|string
     */
    public function getUserHoursGoal(int $userId): ?string
    {
        return static::select('hours_goal')->where(['user_id' => $userId])->value('hours_goal');
    }

    /**
     * A User can have many Notifications
     */
    public function notification()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    /**
     * Defined has many relation for the favorite_mission table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function FavouriteMission(): HasMany
    {
        return $this->hasMany(FavouriteMission::class, 'user_id', 'user_id');
    }

    /**
     * Defined has many relation for the Message table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Message(): HasMany
    {
        return $this->hasMany(Message::class, 'user_id', 'user_id');
    }

    /**
     * Defined has many relation for the mission_application table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionApplication(): HasMany
    {
        return $this->hasMany(MissionApplication::class, 'user_id', 'user_id');
    }

    /**
     * Defined has many relation for the mission_application table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionInviteFromUserId(): HasMany
    {
        return $this->hasMany(MissionInvite::class, 'from_user_id', 'user_id');
    }

    /**
     * Defined has many relation for the mission_application table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionInviteToUserId(): HasMany
    {
        return $this->hasMany(MissionInvite::class, 'to_user_id', 'user_id');
    }

    /**
     * Defined has many relation for the mission_rating table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionRating(): HasMany
    {
        return $this->hasMany(MissionRating::class, 'user_id', 'user_id');
    }

    /**
     * Defined has many relation for the timesheet table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timesheet(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'user_id', 'user_id');
    }

    /**
     * Defined has many relation for the user_skill table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userSkill(): HasMany
    {
        return $this->hasMany(UserSkill::class, 'user_id', 'user_id');
    }

    /**
     * Defined has many relation for the user_filter table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userFilter(): HasMany
    {
        return $this->hasMany(UserFilter::class, 'user_id', 'user_id');
    }

    /**
     * Defined has many relation for the user_notification table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userNotification(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id', 'user_id');
    }



    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skills()
    {
        return $this->hasMany('App\Models\UserSkill', 'user_id', 'user_id');
    }

    /**
    * Defined has many relation for the timesheet table.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'user_id');
    }

    /**
    * Defined has many relation for the message table.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'user_id');
    }

    /**
    * Defined has many relation for the comment table.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    /**
    * Defined has many relation for the stories table.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'user_id');
    }

    /**
    * Defined has many relation for the story invites table.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function storyInvites(): HasMany
    {
        return $this->hasMany(StoryInvite::class, 'from_user_id');
    }

}
