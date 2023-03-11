<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Mission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionDocument extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_document';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_document_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mission_id', 'document_name', 'document_type', 'document_path', 'sort_order'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'mission_document_id',
        'document_name',
        'document_type',
        'document_path',
        'sort_order',
        'updated_at'
    ];

    /**
     * Store/update specified resource.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\MissionDocument
     */
    public function createOrUpdateDocument(array $condition, array $data): MissionDocument
    {
        return static::updateOrCreate($condition, $data);
    }

    /**
     * Soft delete the mission document from the database.
     *
     * @param int $documentId
     * @return bool
     */
    public function deleteDocument(int $documentId): bool
    {
        return static::findOrFail($documentId)->delete();
    }
}
