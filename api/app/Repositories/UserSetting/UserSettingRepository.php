<?php
namespace App\Repositories\UserSetting;

use App\Repositories\UserSetting\UserSettingInterface;
use Illuminate\Http\Request;
use App\User;

class UserSettingRepository implements UserSettingInterface
{
    /**
     * Create a new user setting repository instance.
     *
     * @return void
     */
    public function __construct(
        User $user
    ) {
        $this->user = $user;
    }

    public function saveUserData(int $userId, array $updateData)
    {
        $user = $this->user->find($userId);
        $user->update($updateData);
    }

    public function getUserPreferenceData(int $userId)
    {
        return $this->user->where('user_id', $userId)->select(
            'language_id',
            'timezone_id',
            'currency'
        )->first();
    }
}
