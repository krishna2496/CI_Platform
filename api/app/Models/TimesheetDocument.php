<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimesheetDocument extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'timesheet_document';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'timesheet_document_id';
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = ['timesheet_id', 'document_name', 'document_path', 'document_type'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['timesheet_document_id', 'timesheet_id', 'document_name', 'document_path', 'document_type'];

    /**
     * Soft delete the model from the database.
     *
     * @param int $documentId
     * @param int $timesheetId
     * @return bool
     */
    public function deleteTimesheetDocument(int $documentId, int $timesheetId): bool
    {
        return static::where(['timesheet_id' => $timesheetId,
        'timesheet_document_id' => $documentId])->firstOrFail()->delete();
    }
}
