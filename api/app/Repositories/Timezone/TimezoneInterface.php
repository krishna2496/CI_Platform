<?php
namespace App\Repositories\Timezone;

use App\Models\Timezone;
use Illuminate\Support\Collection;

interface TimezoneInterface
{
    /**
     * Display timezone
     *
     * @param int $timezone_id
     * @return App\Models\Timezone
     */
    public function timezoneList(int $timezone_id = null) :?Timezone;

    /**
     * Get timezone list
     *
     * @return Illuminate\Support\Collection
     */
    public function getTimezoneList() :Collection;
}
