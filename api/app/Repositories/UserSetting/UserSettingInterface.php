<?php
namespace App\Repositories\UserSetting;

use Illuminate\Http\Request;
use App\Models\UserPrivacySetting;
use App\User;

interface UserSettingInterface
{
    /**
     * Store or users data.
     *
     * @param int $userId
     * @param array $requestData
     */
    public function saveUserData(int $userId, array $requestData);

    /**
     * get users preference data
     *
     * @param int $userId
     */
    public function getUserPreferenceData(int $userId);
}
