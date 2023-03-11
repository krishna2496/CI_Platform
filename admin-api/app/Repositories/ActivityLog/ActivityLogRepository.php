<?php
namespace App\Repositories\ActivityLog;

use App\Models\ActivityLog;
use App\Repositories\ActivityLog\ActivityLogInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;

class ActivityLogRepository implements ActivityLogInterface
{
    /**
     * @var App\Models\ActivityLog
     */
    public $activityLog;

    /**
     * Create a new ActivityLog repository instance.
     *
     * @param  App\Models\ActivityLog $activityLog
     * @return void
     */
    public function __construct(ActivityLog $activityLog)
    {
        $this->activityLog = $activityLog;
    }

    /**
     * Get ActivityLog type id
     *
     * @param string $type
     * @return void
     */
    public function storeActivityLog(array $data)
    {
        $this->activityLog->create($data);
    }

    /**
     * Display a listing of specified resources with pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getActivityLogs(Request $request): LengthAwarePaginator
    {
        $type = $request->type;
        $action = $request->action;
        $order = !empty($request->order) ? $request->order : 'desc';
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $activityLogQuery = $this->activityLog
            ->when($type, function ($query, $type) {
                return $query->where('type', $type);
            })->when($action, function ($query, $action) {
                return $query->where('action', $action);
            });

        if (!empty($fromDate) && !empty($toDate)) {
            $activityLogQuery->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate]);
        }

        return $activityLogQuery->orderBy('created_at', $order)->paginate($request->perPage);
    }

    /**
     * Delete activity log related tenant
     *
     * @param int $tenantId
     * @return bool
     */
    public function deleteTenantActivityLog($tenantId): bool
    {
        return $this->activityLog
        ->whereIn('type', [
            config("constants.activity_log_types")["TENANT"]
        ])->where([
            'object_id' => $tenantId
        ])->delete();
    }

    /**
     * Delete api_user related to tenant
     *
     * @param int $tenantId
     * @return bool
     */
    public function deleteTenantApiUserActivityLog($tenantId): bool
    {
        return $this->activityLog
        ->rightJoin('api_user', 'api_user.api_user_id', '=', 'activity_log.object_id')
        ->where('tenant_id', $tenantId)
        ->whereIn('type', [
            config("constants.activity_log_types")["API_USER"]
        ])
        ->delete();
    }

    /**
     * Delete tenant_language related to tenant
     *
     * @param int $tenantId
     * @return bool
     */
    public function deleteTenantLanguageActivityLog($tenantId): bool
    {
        return $this->activityLog
        ->rightJoin('tenant_language', 'tenant_language.tenant_language_id', '=', 'activity_log.object_id')
        ->where('tenant_id', $tenantId)
        ->whereIn('type', [
            config("constants.activity_log_types")["TENANT_LANGUAGE"]
        ])
        ->delete();
    }
}


