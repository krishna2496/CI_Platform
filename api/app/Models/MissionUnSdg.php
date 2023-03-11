<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class MissionUnSdg extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_un_sdg';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_un_sdg_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['un_sdg_number'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mission_id', 'un_sdg_number'];

    /**
     * Get the mission associated with the UN SDG.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mission(): HasMany
    {
        return $this->hasMany(Mission::class, 'mission_id', 'mission_id');
    }

    /**
    * Binds creating/saving events to create UUIDs.
    *
    * @return void
    */
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Generate UUID
            $model->mission_un_sdg_id = Uuid::uuid4()->toString();
        });
    }
}
