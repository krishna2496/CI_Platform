<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TimeMission extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'time_mission';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'time_mission_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mission_id', 'application_deadline', 'application_start_date', 'application_end_date',
     'application_start_time','application_end_time'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['mission_id', 'application_deadline', 'application_start_date',
    'application_end_date', 'application_start_time', 'application_end_time'];

    /**
     * Set application deadline
     *
     * @param  mixed   $value
     * @return void
     */
    public function setApplicationDeadlineAttribute($value)
    {
        $this->attributes['application_deadline'] = ($value !== null) ?
        Carbon::parse($value, config('constants.TIMEZONE'))->setTimezone(config('app.TIMEZONE')) : null;
    }

    /**
     * Get application deadline attribute from the model.
     *
     * @return string
     */
    public function getApplicationDeadlineAttribute()
    {
        if (isset($this->attributes['application_deadline']) && !empty(config('constants.TIMEZONE'))) {
            return Carbon::parse($this->attributes['application_deadline'])
            ->setTimezone(config('constants.TIMEZONE'))->format(config('constants.DB_DATE_TIME_FORMAT'));
        }
    }

    /**
     * Set application start date
     *
     * @param  mixed $value
     * @return void
     */
    public function setApplicationStartDateAttribute($value)
    {
        $this->attributes['application_start_date'] = ($value !== null) ?
        Carbon::parse($value, config('constants.TIMEZONE'))->setTimezone(config('app.TIMEZONE')) : null;
    }

    /**
     * Get application start date attribute from the model.
     *
     * @return string
     */
    public function getApplicationStartDateAttribute()
    {
        if (isset($this->attributes['application_start_date']) && !empty(config('constants.TIMEZONE'))) {
            return Carbon::parse($this->attributes['application_start_date'])
            ->setTimezone(config('constants.TIMEZONE'))->format(config('constants.DB_DATE_FORMAT'));
        }
    }

    /**
     * Set application end date
     *
     * @param  mixed $value
     * @return void
     */
    public function setApplicationEndDateAttribute($value)
    {
        $this->attributes['application_end_date'] = ($value !== null) ?
        Carbon::parse($value, config('constants.TIMEZONE'))->setTimezone(config('app.TIMEZONE')) : null;
    }

    /**
     * Get application end date attribute from the model.
     *
     * @return string
     */
    public function getApplicationEndDateAttribute()
    {
        if (isset($this->attributes['application_end_date']) && !empty(config('constants.TIMEZONE'))) {
            return Carbon::parse($this->attributes['application_end_date'])
            ->setTimezone(config('constants.TIMEZONE'))->format(config('constants.DB_DATE_FORMAT'));
        }
    }

    /**
     * Set application start time attribute on the model.
     *
     * @param  mixed $value
     * @return void
     */
    public function setApplicationStartTimeAttribute($value)
    {
        $this->attributes['application_start_time'] = ($value !== null) ?
        Carbon::parse($value, config('constants.TIMEZONE'))->setTimezone(config('app.TIMEZONE')) : null;
    }

    /**
     * Get application start time attribute from the model.
     *
     * @return string
     */
    public function getApplicationStartTimeAttribute()
    {
        if (isset($this->attributes['application_start_time']) && !empty(config('constants.TIMEZONE'))) {
            return Carbon::parse($this->attributes['application_start_time'])
            ->setTimezone(config('constants.TIMEZONE'))->format(config('constants.DB_DATE_TIME_FORMAT'));
        }
    }

    /**
     * Set application end time attribute on the model.
     *
     * @param  mixed $value
     * @return void
     */
    public function setApplicationEndTimeAttribute($value)
    {
        $this->attributes['application_end_time'] = ($value !== null) ?
        Carbon::parse($value, config('constants.TIMEZONE'))->setTimezone(config('app.TIMEZONE')) : null;
    }

    /**
     * Get application end time attribute from the model.
     *
     * @return string
     */
    public function getApplicationEndTimeAttribute()
    {
        if (isset($this->attributes['application_end_time']) && !empty(config('constants.TIMEZONE'))) {
            return Carbon::parse($this->attributes['application_end_time'])
            ->setTimezone(config('constants.TIMEZONE'))->format(config('constants.DB_DATE_TIME_FORMAT'));
        }
    }

    /**
    * Get deadline for mission application.
    *
    * @param int $missionId
    * @return null|string
    */
    public function getApplicationDeadLine(int $missionId): ?string
    {
        return $this->where('mission_id', $missionId)->value('application_deadline');
    }

    /**
    * Get time mission details for mission application.
    *
    * @param int $missionId
    * @return Collection
    */
    public function getTimeMissionDetails(int $missionId): Collection
    {
        return $this->where('mission_id', $missionId)->get();
    }
}
