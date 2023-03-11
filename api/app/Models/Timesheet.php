<?php
namespace App\Models;

use App\Models\Mission;
use App\Models\TimesheetDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class Timesheet extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'timesheet';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'timesheet_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['timesheet_id', 'user_id', 'mission_id', 'time', 'action', 'date_volunteered',
        'day_volunteered', 'notes', 'status'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'timesheet_id',
        'user_id',
        'mission_id',
        'time',
        'action',
        'date_volunteered',
        'day_volunteered',
        'notes',
        'timesheetDocument',
        'mission',
        'month',
        'total_hours',
        'total_minutes',
        'status',
        'updated_at',
        'user',
        'total_timesheet',
        'first_volunteered_date',
        'total_goal_hours',
        'total_timesheet_action',
        'total_timesheet_time',
        'total_time_seconds'
    ];

    /*
    * Iatstuti\Database\Support\CascadeSoftDeletes;
    */
    protected $cascadeDeletes = ['timesheetDocument'];

    /**
     * Get date volunteered attribute on the model.
     *
     * @return null|string
     */
    public function getDateVolunteeredAttribute(): ?string
    {
        return ($this->attributes['date_volunteered'] !== null) ?
        (new Carbon($this->attributes['date_volunteered']))->format('m-d-Y') : null;
    }

    /**
     * Get the timesheet document record associated with the timesheet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timesheetDocument(): HasMany
    {
        return $this->hasMany(TimesheetDocument::class, 'timesheet_id', 'timesheet_id');
    }

    /**
     * Find the specified resource.
     *
     * @param  int  $id
     * @return array
     */
    public function findTimesheet(int $timesheetId, int $userId)
    {
        return static::with('timesheetDocument')->where(['timesheet_id' => $timesheetId,
            'user_id' => $userId])->firstOrFail();
    }

    /**
     * Get time attribute on the model.
     *
     * @return null|string
     */
    public function getTimeAttribute(): ?string
    {
        return ($this->attributes['time'] !== null) ? date('H:i', strtotime($this->attributes['time'])) : null;
    }

    /**
     * Set note attribute on the model.
     *
     * @param string $value
     * @return void
     */
    public function setNotesAttribute(string $value)
    {
        $this->attributes['notes'] = trim($value);
    }

    /**
     * Get the timesheet mission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mission(): HasOne
    {
        return $this->hasOne(Mission::class, 'mission_id', 'mission_id');
    }

    /**
     * Get the timesheet user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }

    /**
     * Scope a query all the approved timesheets
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsApproved($query)
    {
        $status = [
            config("constants.timesheet_status.APPROVED"),
            config("constants.timesheet_status.AUTOMATICALLY_APPROVED")
        ];

        return $query->whereIn('status', $status);
    }

    /**
     * Scope a query all by timesheets year
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  Int  $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsYear($query, $year = null)
    {
        if (!$year) {
            return $query;
        }

        return $query->whereYear('created_at', $year);
    }

    /**
     * Scope a query all by timesheets month
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  Int $month
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsMonth($query, $month = null)
    {
        if (!$month) {
            return $query;
        }

        return $query->whereMonth('created_at', $month);
    }
}
