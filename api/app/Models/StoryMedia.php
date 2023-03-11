<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoryMedia extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'story_media';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'story_media_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['story_id', 'type', 'path'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['story_media_id','story_id', 'type', 'path'];
    
    /**
     * Soft delete story image from the database.
     *
     * @param int $mediaId
     * @param int $storyId
     * @return bool
     */
    public function deleteStoryImage(int $mediaId, int $storyId): bool
    {
        return static::where(['story_id' => $storyId,
        'story_media_id' => $mediaId])->firstOrFail()->delete();
    }
}
