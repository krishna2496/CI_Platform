<?php

namespace App\Models;

use App\Models\Availability;
use App\Models\Comment;
use App\Models\Country;
use App\Models\DonationAttribute;
use App\Models\FavouriteMission;
use App\Models\GoalMission;
use App\Models\MissionApplication;
use App\Models\MissionDocument;
use App\Models\MissionImpact;
use App\Models\MissionImpactDonation;
use App\Models\MissionInvite;
use App\Models\MissionLanguage;
use App\Models\MissionMedia;
use App\Models\MissionRating;
use App\Models\MissionTab;
use App\Models\MissionUnSdg;
use App\Models\Organization;
use App\Models\State;
use App\Models\TimeMission;
use App\Models\Timesheet;
use App\Models\VolunteeringAttribute;
use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Mission extends Model
{
    use SoftDeletes, Notifiable, CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_id';

    /*
     * @var App\Helpers\Helpers
     */

    private $helpers;

    /**
     * @var App\Models\MissionTab
     */
    public $missionTab;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['theme_id', 'city_id', 'state_id',
    'country_id', 'start_date', 'end_date', 'available_seats',
    'publication_status', 'organization_id', 'mission_type',
    'organisation_detail'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['mission_id', 'theme_id', 'city_id', 'state_id',
    'country_id', 'start_date', 'end_date', 'available_seats',
    'publication_status', 'organisation_detail', 'mission_type',
    'missionDocument', 'missionMedia', 'missionLanguage', 'missionTheme', 'city',
    'default_media_type', 'default_media_path', 'default_media_name', 'title', 'short_description',
    'description', 'objective', 'set_view_detail', 'city_name',
    'seats_left', 'user_application_count', 'mission_application_count', 'missionSkill', 'missionApplication',
    'country', 'favouriteMission', 'missionInvite', 'missionRating', 'goalMission', 'timeMission', 'application_deadline',
    'application_start_date', 'application_end_date', 'application_start_time', 'application_end_time',
    'goal_objective', 'achieved_goal', 'mission_count', 'mission_rating_count',
    'already_volunteered', 'total_available_seat', 'available_seat', 'deadline',
    'favourite_mission_count', 'mission_rating', 'is_favourite', 'skill_id',
    'user_application_status', 'skill', 'rating', 'mission_rating_total_volunteers',
    'availability_id', 'availability_type', 'average_rating', 'timesheet', 'total_hours', 'time',
    'hours', 'action', 'ISO', 'total_minutes', 'custom_information', 'total_timesheet_time', 'total_timesheet_action', 'total_timesheet',
    'mission_title', 'mission_objective', 'label_goal_achieved', 'label_goal_objective', 'state', 'state_name', 'organization', 'organization_name', 'missionTabs', 'volunteeringAttribute',
    'unSdg', 'is_virtual', 'total_seats', 'impact', 'donationAttribute', 'impactDonation', 'donation_statistics'];

    /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['missionDocument','missionMedia','missionLanguage',
        'favouriteMission','missionInvite','missionRating','missionApplication','missionSkill',
        'goalMission','timeMission','comment','timesheet', 'missionTabs', 'volunteeringAttribute', 'impact', 'donationAttribute', 'impactDonation'
    ];

    /**
     * Get the document record associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionDocument(): HasMany
    {
        return $this->hasMany(MissionDocument::class, 'mission_id', 'mission_id');
    }

    /**
     * Get the media record associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionMedia(): HasMany
    {
        return $this->hasMany(MissionMedia::class, 'mission_id', 'mission_id');
    }

    /**
     * Get the language title record associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionLanguage(): HasMany
    {
        return $this->hasMany(MissionLanguage::class, 'mission_id', 'mission_id');
    }

    /**
     * Get the mission theme associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function missionTheme(): BelongsTo
    {
        return $this->belongsTo(MissionTheme::class, 'theme_id', 'mission_theme_id');
    }

    /**
     * Get city associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function city(): HasOne
    {
        return $this->hasOne(City::class, 'city_id', 'city_id')
         ->select('city_id', 'state_id');
    }

    /**
     * Get country associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'country_id', 'country_id');
    }

    /**
     * Get favourite mission associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favouriteMission(): HasMany
    {
        return $this->hasMany(FavouriteMission::class, 'mission_id', 'mission_id');
    }

    /**
     * Get invite mission associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionInvite(): HasMany
    {
        return $this->hasMany(MissionInvite::class, 'mission_id', 'mission_id');
    }

    /**
     * Get rating associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionRating(): HasMany
    {
        return $this->hasMany(MissionRating::class, 'mission_id', 'mission_id');
    }

    /**
     * Get the mission application associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionApplication(): HasMany
    {
        return $this->hasMany(MissionApplication::class, 'mission_id', 'mission_id');
    }

    /**
     * Get the mission skill associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionSkill(): HasMany
    {
        return $this->hasMany(MissionSkill::class, 'mission_id', 'mission_id');
    }

    /**
     * Defined for goal mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function goalMission(): HasOne
    {
        return $this->hasOne(GoalMission::class, 'mission_id', 'mission_id');
    }

    /**
     * Defined for time mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function timeMission(): HasOne
    {
        return $this->hasOne(TimeMission::class, 'mission_id', 'mission_id');
    }

    /**
     * Get comment associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class, 'mission_id', 'mission_id');
    }


    /**
     * Get timesheet associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timesheet(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'mission_id', 'mission_id');
    }

    /**
     * Soft delete from the database.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteMission(int $id): bool
    {
        return static::findOrFail($id)->delete();
    }

    /**
     * Get an attribute from the model.
     *
     * @return string
     */
    public function getCityTranslationAttribute(): object
    {
        return $this->city->hasMany(CityLanguage::class, 'city_id', 'city_id')->get();
    }

    /**
     * Set start date attribute on the model.
     *
     * @param $value
     */
    public function setStartDateAttribute($value): void
    {
        $this->attributes['start_date'] = (($value !== null) && strlen(trim($value)) > 0) ?
        Carbon::parse($value, config('constants.TIMEZONE'))->setTimezone(config('app.TIMEZONE')) : null;
    }

    /**
     * Get start date attribute from the model.
     *
     * @return string
     */
    public function getStartDateAttribute(): ?string
    {
        return (isset($this->attributes['start_date']) && !empty(config('constants.TIMEZONE'))) ?
        Carbon::parse($this->attributes['start_date'])->setTimezone(config('constants.TIMEZONE'))
            ->format(config('constants.DB_DATE_TIME_FORMAT')) :
            null;
    }

    /**
     * Set end date attribute on the model.
     *
     * @param $value
     */
    public function setEndDateAttribute($value): void
    {
        $this->attributes['end_date'] = ($value !== null && strlen(trim($value)) > 0) ?
        Carbon::parse($value, config('constants.TIMEZONE'))->setTimezone(config('app.TIMEZONE')) : null;
    }

    /**
     * Get end date attribute from the model.
     *
     * @return string|null
     */
    public function getEndDateAttribute(): ?string
    {
        return (isset($this->attributes['end_date']) && !empty(config('constants.TIMEZONE'))) ?
             Carbon::parse($this->attributes['end_date'])->setTimezone(config('constants.TIMEZONE'))
             ->format(config('constants.DB_DATE_TIME_FORMAT')) :
             null;
    }

    /**
     * Check seats are available or not.
     *
     * @param int $missionId
     *
     * @return App\Models\Mission
     */
    public function checkAvailableSeats(int $missionId): Mission
    {
        return $this->select('*')
        ->with(['volunteeringAttribute'])
        ->where('mission.mission_id', $missionId)
        ->withCount(['missionApplication as mission_application_count' => function ($query) use ($missionId) {
            $query->whereIn('approval_status', [config('constants.application_status')['AUTOMATICALLY_APPROVED'],
            ]);
        }])->first();
    }

    /**
     * Set organisation detail in json_encode form
     *
     * @param array|null $value
     */
    public function setOrganisationDetailAttribute($value)
    {
        if (!is_null($value) && !empty($value)) {
            $this->attributes['organisation_detail'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * @param $value
     * @return mixed|null
     */
    public function getOrganisationDetailAttribute($value)
    {
        if (!is_null($value) && ($value !== '')) {
            $data = @json_decode($value);

            if ($data !== null) {
                return json_decode($value, true);
            }
        }

        return null;
    }

    /**
     * Get Organization associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class, 'organization_id', 'organization_id');
    }

    /**
     * Get mission donation impact with the mission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function impactDonation(): HasMany
    {
        return $this->hasMany(MissionImpactDonation::class, 'mission_id', 'mission_id');
    }

    /**
    * Get volunteering attribute associated with the mission.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function volunteeringAttribute(): HasOne
    {
        return $this->hasOne(VolunteeringAttribute::class, 'mission_id', 'mission_id');
    }

    /**
     * Set impact mission attribute on the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function impact(): HasMany
    {
        return $this->hasMany(MissionImpact::class, 'mission_id', 'mission_id')->orderBy('sort_key');
    }

    /**
     * Get mission-tab associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionTabs(): HasMany
    {
        return $this->hasMany(MissionTab::class, 'mission_id', 'mission_id')->orderBy('sort_key');
    }

    /**
     * Get donation attribute associated with mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function donationAttribute(): HasOne
    {
        return $this->hasOne(DonationAttribute::class, 'mission_id', 'mission_id');
    }

    /**
     * Get UN SDG associated with mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unSdg(): HasMany
    {
        return $this->hasMany(MissionUnSdg::class, 'mission_id', 'mission_id')->orderBy('un_sdg_number');
    }

    /**
     * Query all the approved mission
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsApproved($query)
    {
        return $query->whereIn('publication_status', [
            config('constants.publication_status.APPROVED')
        ]);
    }

    /**
     * Query all the donation types mission
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsDonationTypes($query)
    {
        return $query->whereIn('mission_type', config('constants.donation_mission_types'));
    }
}
