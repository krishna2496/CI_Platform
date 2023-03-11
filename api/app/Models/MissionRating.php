<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionRating extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_rating';

    /**
     * The primary key for the model.
     *
     * @var int
     */
    protected $primaryKey = 'mission_rating_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mission_id', 'user_id', 'rating'];
    
    /**
     * Store/update specified resource.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\MissionRating
     */
    public function createOrUpdateRating(array $condition, array $data): MissionRating
    {
        return static::updateOrCreate($condition, $data);
    }
}
