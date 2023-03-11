<?php
namespace App\Repositories\ActivityLog;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\UserNotification;
use Illuminate\Pagination\LengthAwarePaginator;

interface ActivityLogInterface
{
    /**
     * Store activity data into database
     *
     * @param array $data
     * @return array
     */
    public function storeActivityLog(array $data);

    /**
     * Display a listing of specified resources with pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getActivityLogs(Request $request): LengthAwarePaginator;
}
